import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { nextTick } from 'vue';
import { mount } from '@vue/test-utils';
import UiAlert from '@/vue/Components/Admin/Ui/Feedback/UiAlert.vue';
import UiToastRegion from '@/vue/Components/Admin/Ui/Feedback/UiToastRegion.vue';
import UiEmptyState from '@/vue/Components/Admin/Ui/Feedback/UiEmptyState.vue';
import UiSkeleton from '@/vue/Components/Admin/Ui/Feedback/UiSkeleton.vue';
import { notificationState, notify } from '@/vue/Composables/Global/toast';
import { withLabels } from '../../__tests__/support';

describe('UiAlert', () => {
  it('annonce poliment une information, avec insistance un danger', () => {
    expect(mount(UiAlert, withLabels({ props: { variant: 'info' } })).attributes('role')).toBe('status');
    expect(mount(UiAlert, withLabels({ props: { variant: 'danger' } })).attributes('role')).toBe('alert');
  });

  it('se ferme sur demande', async () => {
    const wrapper = mount(UiAlert, withLabels({ props: { dismissible: true, title: 'Tag utilisé' }, slots: { default: 'Sur 12 pages' } }));
    expect(wrapper.text()).toContain('Tag utilisé');
    await wrapper.get('[aria-label="alert.close"]').trigger('click');
    expect(wrapper.emitted('close')).toHaveLength(1);
  });
});

describe('UiToastRegion', () => {
  beforeEach(() => {
    vi.useFakeTimers();
    notificationState.items.splice(0);
  });
  afterEach(() => vi.useRealTimers());

  it('affiche les trois plus récentes, la dernière en tête', async () => {
    const wrapper = mount(UiToastRegion, withLabels());
    ['Un', 'Deux', 'Trois', 'Quatre'].forEach((title) => notify('success', title));
    await nextTick();
    const titles = wrapper.findAll('[role="status"] p').map((p) => p.text());
    expect(titles).toEqual(['Quatre', 'Trois', 'Deux']);
  });

  it('se ferme après sa durée, suspendue au survol', async () => {
    const wrapper = mount(UiToastRegion, withLabels());
    notify('danger', 'Erreur', 'Le tag est utilisé', 5000);
    await nextTick();
    const toast = wrapper.get('[role="alert"]');
    vi.advanceTimersByTime(3000);
    await toast.trigger('mouseenter');
    vi.advanceTimersByTime(10000);
    await nextTick();
    expect(wrapper.find('[role="alert"]').exists()).toBe(true);
    await toast.trigger('mouseleave');
    vi.advanceTimersByTime(2100);
    await nextTick();
    expect(wrapper.find('[role="alert"]').exists()).toBe(false);
  });

  it('se ferme au bouton', async () => {
    const wrapper = mount(UiToastRegion, withLabels());
    notify('info', 'Rien à désactiver');
    await nextTick();
    await wrapper.get('[aria-label="toast.close"]').trigger('click');
    expect(notificationState.items).toHaveLength(0);
  });
});

describe('UiEmptyState et UiSkeleton', () => {
  it('affiche le titre et le texte', () => {
    const wrapper = mount(UiEmptyState, { props: { title: 'Aucun tag', text: 'Créez le premier' } });
    expect(wrapper.get('h3').text()).toBe('Aucun tag');
  });

  it('masque le squelette aux lecteurs d’écran', () => {
    const wrapper = mount(UiSkeleton, { props: { lines: 2 } });
    expect(wrapper.attributes('aria-hidden')).toBe('true');
    expect(wrapper.findAll('.ui-skeleton')).toHaveLength(2);
  });
});
