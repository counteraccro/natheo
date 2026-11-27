import { describe, expect, it, vi } from 'vitest';
import { ref, shallowRef } from 'vue';
import { flushPromises, mount } from '@vue/test-utils';
import { createMemoryHistory, createRouter } from 'vue-router';
import CommentStatusBanner from '@admin/Modules/Comment/CommentStatusBanner.vue';
import CommentModerateDialog from '@admin/Modules/Comment/CommentModerateDialog.vue';
import CommentStatusNote from '@admin/Modules/Comment/CommentStatusNote.vue';
import CommentPageLink from '@admin/Modules/Comment/CommentPageLink.vue';
import CommentStatusBadge from '@admin/Modules/Comment/CommentStatusBadge.vue';
import { ADMIN_CONTEXT, createAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { notifyResourceChange } from '@/vue/Composables/Admin/Screen/resourceChanges';
import { bootstrap, itemOf } from '@tests/vue/Composables/Admin/support';
import { labels } from '@tests/vue/Components/Admin/Ui/support';
import { UI_LABELS } from '@/vue/Composables/Admin/Ui/useUiLabels';
import type { ApiClient } from '@/vue/Composables/Global/Api/ApiClient';
import type { AdminBootstrap } from '@/ts/Admin/App/AdminBootstrap.type';
import type { CollectionScreenState } from '@/ts/Admin/Screen/CollectionScreen.type';
import type { FormScreenState } from '@/ts/Admin/Screen/FormScreen.type';
import type { AdminComment } from '@/ts/Admin/Api/AdminComment.type';

const t = (key: string, params: Record<string, unknown> = {}): string =>
  Object.keys(params).length ? `${key} ${JSON.stringify(params)}` : key;

function globalWith(api: object = {}, values: Partial<AdminBootstrap> = {}) {
  const context = { ...createAdminContext(bootstrap(values)), t, api: api as ApiClient };
  const router = createRouter({
    history: createMemoryHistory('/admin/fr/'),
    routes: [
      {
        path: '/comments',
        name: 'comment_index',
        component: { render: () => null },
        meta: { screenGroup: 'comment_index' },
      },
    ],
  });
  return {
    context,
    router,
    global: { plugins: [router], provide: { [ADMIN_CONTEXT as symbol]: context, [UI_LABELS as symbol]: labels } },
  };
}

describe('CommentStatusBanner', () => {
  const screenWith = (items: Partial<AdminComment>[] = []) =>
    ({
      descriptor: { resource: '/admin/comments' },
      collection: { items: shallowRef(items) },
      filter: { moderation: ref(''), pageId: ref('') },
    }) as unknown as CollectionScreenState<AdminComment>;

  it('affiche le nombre par statut, filtre au clic et se recharge après une écriture', async () => {
    const getCollection = vi.fn().mockResolvedValue({
      members: [
        { status: 'wait_validation', count: 3 },
        { status: 'validate', count: 12 },
        { status: 'moderate', count: 1 },
      ],
    });
    const { global, context } = globalWith({ getCollection });
    context.menu.items.value = [itemOf('option_system', { legacyUrl: '/admin/fr/options-system' })];
    const screen = screenWith();
    const wrapper = mount(CommentStatusBanner, { props: { screen }, global });
    await flushPromises();

    const counters = wrapper.findAll('button[aria-pressed]');
    expect(counters.map((button) => button.text())).toEqual([
      'comment.state.wait_validation3',
      'comment.state.validate12',
      'comment.state.moderate1',
    ]);
    await counters[0].trigger('click');
    expect(screen.filter.moderation.value).toBe('wait_validation');
    expect(counters[0].attributes('aria-pressed')).toBe('true');
    await counters[0].trigger('click');
    expect(screen.filter.moderation.value).toBe('');

    expect(wrapper.text()).toContain('comment.banner.open');
    expect(wrapper.text()).toContain('comment.banner.wait_on');
    expect(wrapper.get('a').attributes('href')).toBe('/admin/fr/options-system');

    notifyResourceChange('/admin/comments', { ids: [1] });
    await flushPromises();
    expect(getCollection).toHaveBeenCalledTimes(2);
    wrapper.unmount();
  });

  it('rappelle des commentaires fermés, sans lien vers des options non accessibles', async () => {
    const getCollection = vi.fn().mockResolvedValue({ members: [] });
    const base = bootstrap();
    const { global } = globalWith({ getCollection }, { options: { ...base.options, openComment: false } });
    const wrapper = mount(CommentStatusBanner, { props: { screen: screenWith() }, global });
    await flushPromises();
    expect(wrapper.text()).toContain('comment.banner.closed');
    expect(wrapper.text()).not.toContain('comment.banner.wait_on');
    expect(wrapper.find('a').exists()).toBe(false);
  });

  it('montre le filtre par page avec son titre et le retire', async () => {
    const { global } = globalWith({ getCollection: vi.fn().mockResolvedValue({ members: [] }) });
    const screen = screenWith([{ id: 1, page: { id: 4, title: 'Accueil', url: null } }]);
    screen.filter.pageId.value = '4';
    const wrapper = mount(CommentStatusBanner, { props: { screen }, global });
    await flushPromises();
    expect(wrapper.text()).toContain('comment.banner.page_filter {"title":"Accueil"}');
    await wrapper.get('button[aria-label="comment.banner.page_filter_remove"]').trigger('click');
    expect(screen.filter.pageId.value).toBe('');
  });
});

describe('CommentModerateDialog', () => {
  it('envoie le commentaire de modération, null quand il est vide, ou annule', async () => {
    const { global } = globalWith();
    const wrapper = mount(CommentModerateDialog, { props: { count: 2, kept: 1 }, global, attachTo: document.body });
    await flushPromises();
    expect(wrapper.text()).toContain('comment.dialog.moderate_title {"count":2}');
    expect(wrapper.text()).toContain('comment.dialog.moderate_kept {"kept":1}');

    const confirm = wrapper.findAll('button').find((button) => button.text() === 'comment.dialog.moderate_confirm')!;
    await confirm.trigger('click');
    expect(wrapper.emitted('submit')?.[0]).toEqual([{ moderationComment: null }]);

    const again = mount(CommentModerateDialog, { props: { count: 1, kept: 0 }, global, attachTo: document.body });
    await flushPromises();
    await again.get('textarea').setValue('Hors sujet');
    await again
      .findAll('button')
      .find((button) => button.text() === 'comment.dialog.moderate_confirm')!
      .trigger('click');
    expect(again.emitted('submit')?.[0]).toEqual([{ moderationComment: 'Hors sujet' }]);
    const cancelled = mount(CommentModerateDialog, { props: { count: 1, kept: 0 }, global, attachTo: document.body });
    await flushPromises();
    await cancelled
      .findAll('button')
      .find((button) => button.text() === 'comment.dialog.moderate_cancel')!
      .trigger('click');
    expect(cancelled.emitted('cancel')).toHaveLength(1);
    expect(cancelled.emitted('submit')).toBeUndefined();
    [wrapper, again, cancelled].forEach((mounted) => mounted.unmount());
  });
});

describe('CommentStatusNote', () => {
  const screen = {} as FormScreenState<AdminComment>;
  const moderator = { id: 2, login: 'N-Editeur' };

  it.each([
    [{ status: 'validate', moderator: null }, 'comment.editor.status_help_validate', ''],
    [{ status: 'moderate', moderator: null }, 'comment.editor.status_help_moderate', 'status_help_moderator'],
    [{ status: 'wait_validation', moderator }, 'comment.editor.status_help_wait_validation', 'status_help_cleared'],
  ] as const)('décrit l’effet du statut %o', (data, help, change) => {
    const wrapper = mount(CommentStatusNote, { props: { data, screen }, global: globalWith().global });
    expect(wrapper.text()).toContain(help);
    if (change) expect(wrapper.text()).toContain(change);
    expect(wrapper.text()).not.toContain('moderated_by');
  });

  it('nomme le modérateur d’un commentaire modéré', () => {
    const data = { status: 'moderate' as const, moderator };
    const wrapper = mount(CommentStatusNote, { props: { data, screen }, global: globalWith().global });
    expect(wrapper.text()).toContain('comment.editor.moderated_by');
    expect(wrapper.text()).toContain('N-Editeur');
  });
});

describe('colonnes de la liste', () => {
  it('lie la page à la liste filtrée sur elle, en gardant les autres filtres', async () => {
    const { global, router } = globalWith();
    await router.push('/comments?moderation=moderate');
    const wrapper = mount(CommentPageLink, { props: { value: { id: 4, title: 'Accueil', url: null } }, global });
    expect(wrapper.get('a').attributes('href')).toBe('/admin/fr/comments?moderation=moderate&pageId=4');
    expect(wrapper.text()).toBe('Accueil');
    const empty = mount(CommentPageLink, { props: { value: null }, global });
    expect(empty.text()).toBe('—');
  });

  it('affiche le statut avec son libellé', () => {
    const wrapper = mount(CommentStatusBadge, { props: { value: 'validate' }, global: globalWith().global });
    expect(wrapper.text()).toBe('comment.state.validate');
    expect(wrapper.html()).toContain('text-success');
  });
});
