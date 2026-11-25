import { afterEach, describe, expect, it, vi } from 'vitest';
import { isMac } from '@/vue/Composables/Global/isMac';

describe('isMac', () => {
  afterEach(() => vi.unstubAllGlobals());

  it('reconnaît un système d’Apple par son agent utilisateur', () => {
    vi.stubGlobal('navigator', { userAgent: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_0)' });
    expect(isMac()).toBe(true);
    vi.stubGlobal('navigator', { userAgent: 'Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X)' });
    expect(isMac()).toBe(true);
    vi.stubGlobal('navigator', { userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)' });
    expect(isMac()).toBe(false);
  });
});
