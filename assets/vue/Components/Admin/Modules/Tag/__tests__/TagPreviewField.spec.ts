import { describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import TagPreviewField from '@admin/Modules/Tag/TagPreviewField.vue';
import { ADMIN_CONTEXT, createAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { bootstrap } from '@/vue/Composables/Admin/__tests__/support';
import { labels } from '@/vue/Components/Admin/Ui/__tests__/support';
import { UI_LABELS } from '@/vue/Composables/Admin/Ui/useUiLabels';
import type { FormScreenState } from '@/ts/Admin/Screen/FormScreen.type';
import type { AdminTag } from '@/ts/Admin/Api/AdminTag.type';

/** Hauteur envoyée par l'aperçu de la fenêtre `from` */
const sendHeight = (from: Window | null, height: number): void => {
  window.dispatchEvent(
    new MessageEvent('message', {
      data: { source: 'natheo-preview', type: 'height', height },
      origin: window.location.origin,
      source: from,
    })
  );
};

describe('TagPreviewField', () => {
  it('écoute toujours le cadre de la langue affichée après un retour sur un onglet précédent', async () => {
    const screen = { currentLocale: 'fr', locales: ['fr', 'en', 'es'] } as unknown as FormScreenState<AdminTag>;
    const wrapper = mount(TagPreviewField, {
      props: { data: { color: '#112233', translations: [{ locale: 'fr', label: 'Été' }] }, screen },
      attachTo: document.body,
      global: {
        provide: { [ADMIN_CONTEXT as symbol]: createAdminContext(bootstrap()), [UI_LABELS as symbol]: labels },
      },
    });
    const tabs = wrapper.findAll('[role="tab"]');
    await tabs[1].trigger('click');
    await tabs[0].trigger('click');
    await flushPromises();

    // happy-dom ne charge pas les cadres : la fenêtre de l'aperçu est simulée
    const iframe = wrapper.get('iframe');
    const preview = { postMessage: vi.fn() } as unknown as Window;
    Object.defineProperty(iframe.element, 'contentWindow', { value: preview });
    sendHeight(preview, 180);
    await flushPromises();
    expect(iframe.attributes('style')).toContain('height: 180px');
    wrapper.unmount();
  });
});
