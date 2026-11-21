import { describe, expect, it, vi } from 'vitest';
import { nextTick, watchEffect } from 'vue';
import { createTranslator } from '@/vue/Composables/Admin/useTranslate';

const messages = {
  admin_app: {
    'admin_app.page.title': 'Bonjour {name}',
    'admin_app.page.count': '{count, plural, =0 {Aucun élément} one {# élément} other {# éléments}}',
  },
};

describe('createTranslator', () => {
  it('formate les paramètres et les pluriels ICU', () => {
    const { t } = createTranslator('fr', messages, vi.fn());
    expect(t('admin_app.page.title', { name: 'Ada' })).toBe('Bonjour Ada');
    expect(t('admin_app.page.count', { count: 0 })).toBe('Aucun élément');
    expect(t('admin_app.page.count', { count: 1 })).toBe('1 élément');
    expect(t('admin_app.page.count', { count: 3 })).toBe('3 éléments');
  });

  it('renvoie la clé absente, avec un avertissement unique pour un domaine connu', () => {
    const warn = vi.spyOn(console, 'warn').mockImplementation(() => {});
    const { t } = createTranslator('fr', messages, vi.fn());
    expect(t('admin_app.page.missing')).toBe('admin_app.page.missing');
    t('admin_app.page.missing');
    expect(warn).toHaveBeenCalledTimes(1);
    expect(t('tag.index.title')).toBe('tag.index.title');
    expect(warn).toHaveBeenCalledTimes(1);
  });

  it('garde le message brut quand un paramètre manque', () => {
    const { t } = createTranslator('fr', messages, vi.fn());
    expect(t('admin_app.page.title')).toBe('Bonjour {name}');
  });

  it('charge un domaine absent une seule fois et met à jour les textes déjà affichés', async () => {
    const loader = vi.fn().mockResolvedValue({ 'tag.index.title': 'Tags' });
    const translator = createTranslator('fr', messages, loader);
    let shown = '';
    watchEffect(() => {
      shown = translator.t('tag.index.title');
    });
    expect(shown).toBe('tag.index.title');

    await Promise.all([translator.loadDomain('tag'), translator.loadDomain('tag')]);
    await nextTick();
    expect(loader).toHaveBeenCalledTimes(1);
    expect(translator.hasDomain('tag')).toBe(true);
    expect(shown).toBe('Tags');
    await translator.loadDomain('admin_app');
    expect(loader).toHaveBeenCalledTimes(1);
  });

  it('redemande un domaine dont le chargement a échoué', async () => {
    const loader = vi.fn().mockRejectedValueOnce(new Error('réseau')).mockResolvedValueOnce({});
    const translator = createTranslator('fr', messages, loader);
    await expect(translator.loadDomain('tag')).rejects.toThrow('réseau');
    await translator.loadDomain('tag');
    expect(loader).toHaveBeenCalledTimes(2);
  });
});
