import { describe, expect, it, vi } from 'vitest';
import { createAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { bootstrap } from '@tests/vue/Composables/Admin/support';

function json(body: unknown, status = 200): Response {
  return new Response(JSON.stringify(body), { status, headers: { 'Content-Type': 'application/json' } });
}

describe('createAdminContext', () => {
  it('rejoue un appel refusé avec le token renouvelé', async () => {
    const fetchMock = vi
      .fn<typeof fetch>()
      .mockResolvedValueOnce(json({ title: 'Unauthorized' }, 401))
      .mockResolvedValueOnce(json({ accessToken: 'renouvele', expiresAt: '2099-01-01T00:00:00Z' }))
      .mockResolvedValueOnce(json({ id: 1 }));
    vi.stubGlobal('fetch', fetchMock);

    const context = createAdminContext(bootstrap(), { reload: vi.fn() });
    expect(await context.api.get('/admin/modules/tag')).toEqual({ id: 1 });
    expect((fetchMock.mock.calls[0][1]?.headers as Record<string, string>).Authorization).toBe('Bearer initial');
    expect(fetchMock.mock.calls[1][0]).toBe('/admin/fr/app/token');
    expect((fetchMock.mock.calls[2][1]?.headers as Record<string, string>).Authorization).toBe('Bearer renouvele');
  });

  it('lit un catalogue absent dans l’API et traduit avec les catalogues de la page', async () => {
    const fetchMock = vi
      .fn<typeof fetch>()
      .mockResolvedValue(json({ domain: 'tag', locale: 'fr', messages: { 'tag.index.title': 'Tags' } }));
    vi.stubGlobal('fetch', fetchMock);

    const context = createAdminContext(bootstrap());
    expect(context.t('admin_app.leave.title')).toBe('Quitter la page ?');
    await context.translator.loadDomain('tag');
    expect(fetchMock.mock.calls[0][0]).toBe('/api/v2/admin/translations/tag?locale=fr');
    expect(context.t('tag.index.title')).toBe('Tags');
  });
});
