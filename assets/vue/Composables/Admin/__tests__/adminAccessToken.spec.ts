import { describe, expect, it, vi } from 'vitest';
import { createAdminAccessToken } from '@/vue/Composables/Admin/adminAccessToken';

const NOW = Date.parse('2026-11-19T10:00:00Z');

function tokenResponse(accessToken: string, expiresAt: string): Response {
  return new Response(JSON.stringify({ accessToken, expiresAt }), {
    headers: { 'Content-Type': 'application/json' },
  });
}

function store(expiresAt: string, reload = vi.fn()) {
  return createAdminAccessToken({ accessToken: 'initial', expiresAt }, '/admin/fr/app/token', {
    reload,
    now: () => NOW,
  });
}

describe('createAdminAccessToken', () => {
  it('renvoie le token reçu avec la page tant qu’il ne va pas expirer', async () => {
    const fetchMock = vi.fn<typeof fetch>();
    vi.stubGlobal('fetch', fetchMock);
    expect(await store('2026-11-19T10:10:00Z').token()).toBe('initial');
    expect(fetchMock).not.toHaveBeenCalled();
  });

  it('renouvelle le token qui expire dans moins d’une minute, en une seule requête', async () => {
    const fetchMock = vi.fn<typeof fetch>().mockResolvedValue(tokenResponse('renouvele', '2026-11-19T10:15:00Z'));
    vi.stubGlobal('fetch', fetchMock);
    const tokens = store('2026-11-19T10:00:30Z');

    expect(await Promise.all([tokens.token(), tokens.token()])).toEqual(['renouvele', 'renouvele']);
    expect(fetchMock).toHaveBeenCalledTimes(1);
    expect(fetchMock.mock.calls[0][0]).toBe('/admin/fr/app/token');
    expect(await tokens.token()).toBe('renouvele');
  });

  it('recharge la page quand la session PHP est perdue', async () => {
    vi.stubGlobal('fetch', vi.fn<typeof fetch>().mockResolvedValue(new Response('<html>', { status: 200 })));
    const reload = vi.fn();
    expect(await store('2026-11-19T10:10:00Z', reload).renew()).toBe(false);
    expect(reload).toHaveBeenCalledTimes(1);
  });

  it('recharge la page sur une redirection vers la connexion', async () => {
    const redirect = { ok: false, type: 'opaqueredirect', status: 0, headers: new Headers() } as Response;
    vi.stubGlobal('fetch', vi.fn<typeof fetch>().mockResolvedValue(redirect));
    const reload = vi.fn();
    expect(await store('2026-11-19T10:10:00Z', reload).renew()).toBe(false);
    expect(reload).toHaveBeenCalledTimes(1);
  });

  it('ne recharge pas la page sur une erreur réseau', async () => {
    vi.stubGlobal('fetch', vi.fn<typeof fetch>().mockRejectedValue(new TypeError('réseau')));
    const reload = vi.fn();
    expect(await store('2026-11-19T10:10:00Z', reload).renew()).toBe(false);
    expect(reload).not.toHaveBeenCalled();
  });
});
