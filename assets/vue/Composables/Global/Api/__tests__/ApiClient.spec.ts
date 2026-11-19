import { describe, expect, it, vi } from 'vitest';
import { ApiClient, type ApiClientOptions } from '@/vue/Composables/Global/Api/ApiClient';
import { ApiError } from '@/vue/Composables/Global/Api/ApiError';

function json(body: unknown, status: number = 200, headers: Record<string, string> = {}): Response {
  return new Response(status === 204 ? null : JSON.stringify(body), { status, headers });
}

function mockFetch(...responses: Response[]) {
  const fetchMock = vi.fn<typeof fetch>();
  for (const response of responses) {
    fetchMock.mockResolvedValueOnce(response);
  }
  vi.stubGlobal('fetch', fetchMock);
  return fetchMock;
}

function client(options: Partial<ApiClientOptions> = {}): ApiClient {
  return new ApiClient({ baseUrl: '/api/v2', locale: 'fr', token: () => 'token-1', ...options });
}

function headersOf(fetchMock: ReturnType<typeof mockFetch>, call: number = 0): Record<string, string> {
  return fetchMock.mock.calls[call][1]?.headers as Record<string, string>;
}

describe('ApiClient', () => {
  it('préfixe le chemin, ajoute la locale et les paramètres non vides', async () => {
    const fetchMock = mockFetch(json({ id: 1 }));
    await client().get('/pages', { slug: 'accueil', page: null, empty: '' });
    expect(fetchMock.mock.calls[0][0]).toBe('/api/v2/pages?locale=fr&slug=accueil');
  });

  it('garde une IRI renvoyée par l’API et sa locale', async () => {
    const fetchMock = mockFetch(json({ id: 1 }));
    await client().get('/api/v2/pages/3?locale=en');
    expect(fetchMock.mock.calls[0][0]).toBe('/api/v2/pages/3?locale=en');
  });

  it('envoie le token relu à chaque appel', async () => {
    const fetchMock = mockFetch(json({}), json({}));
    let token = 'token-1';
    const api = client({ token: async () => token });
    await api.get('/me');
    token = 'token-2';
    await api.get('/me');
    expect(headersOf(fetchMock, 0).Authorization).toBe('Bearer token-1');
    expect(headersOf(fetchMock, 1).Authorization).toBe('Bearer token-2');
  });

  it('lit une collection Hydra et sa pagination', async () => {
    mockFetch(json({ member: [{ id: 1 }], totalItems: 45, view: { '@id': '/x', last: '/api/v2/tags?page=3' } }));
    const page = await client().getCollection('/tags', { page: 2 });
    expect(page).toEqual({ members: [{ id: 1 }], total: 45, page: 2, lastPage: 3 });
  });

  it('renvoie la ressource avec son ETag', async () => {
    mockFetch(json({ id: 4 }, 200, { ETag: 'W/"abc"' }));
    expect(await client().getResource('/admin/tags/4')).toEqual({ data: { id: 4 }, etag: 'W/"abc"' });
  });

  it('envoie If-Match et le format merge-patch sur un PATCH', async () => {
    const fetchMock = mockFetch(json({ id: 4 }));
    await client().patch('/admin/tags/4', { label: 'A' }, { ifMatch: '"abc"' });
    expect(fetchMock.mock.calls[0][1]?.method).toBe('PATCH');
    expect(fetchMock.mock.calls[0][1]?.body).toBe('{"label":"A"}');
    expect(headersOf(fetchMock)).toMatchObject({ 'If-Match': '"abc"', 'Content-Type': 'application/merge-patch+json' });
  });

  it('supprime avec If-Match et accepte un 204', async () => {
    const fetchMock = mockFetch(json(null, 204));
    await expect(client().delete('/admin/tags/4', { ifMatch: '"abc"' })).resolves.toBeUndefined();
    expect(fetchMock.mock.calls[0][1]?.method).toBe('DELETE');
    expect(headersOf(fetchMock)['If-Match']).toBe('"abc"');
  });

  it('n’envoie pas If-Match sans ETag', async () => {
    const fetchMock = mockFetch(json({}));
    await client().patch('/admin/tags/4', {});
    expect(headersOf(fetchMock)).not.toHaveProperty('If-Match');
  });

  it('rejoue une fois la requête après un 401 si onUnauthorized renvoie true', async () => {
    const fetchMock = mockFetch(json({ title: 'Unauthorized' }, 401), json({ id: 1 }));
    let token = 'expiré';
    const onUnauthorized = vi.fn(async () => {
      token = 'neuf';
      return true;
    });
    const data = await client({ token: () => token, onUnauthorized }).get('/me');
    expect(data).toEqual({ id: 1 });
    expect(onUnauthorized).toHaveBeenCalledOnce();
    expect(headersOf(fetchMock, 1).Authorization).toBe('Bearer neuf');
  });

  it('ne rejoue pas une seconde fois et renvoie l’erreur', async () => {
    mockFetch(json({ title: 'Unauthorized' }, 401), json({ title: 'Unauthorized' }, 401));
    const onUnauthorized = vi.fn(() => true);
    await expect(client({ onUnauthorized }).get('/me')).rejects.toMatchObject({ status: 401 });
    expect(onUnauthorized).toHaveBeenCalledOnce();
  });

  it('ne rejoue pas si onUnauthorized renvoie false', async () => {
    const fetchMock = mockFetch(json({ title: 'Unauthorized' }, 401));
    await expect(client({ onUnauthorized: () => false }).get('/me')).rejects.toBeInstanceOf(ApiError);
    expect(fetchMock).toHaveBeenCalledOnce();
  });

  it('garde le corps problem+json dans l’erreur', async () => {
    const violations = [{ propertyPath: 'label', message: 'Obligatoire' }];
    mockFetch(json({ title: 'Erreur de validation', detail: 'label: Obligatoire', violations }, 422));
    const error = (await client()
      .post('/admin/tags', {})
      .catch((e: unknown) => e)) as ApiError;
    expect(error).toMatchObject({ status: 422, title: 'Erreur de validation', detail: 'label: Obligatoire' });
    expect(error.body).toMatchObject({ violations });
  });

  it('transforme une erreur réseau en ApiError de statut 0', async () => {
    vi.stubGlobal('fetch', vi.fn().mockRejectedValue(new TypeError('Failed to fetch')));
    await expect(client().get('/me')).rejects.toMatchObject({ status: 0, title: 'Failed to fetch' });
  });
});
