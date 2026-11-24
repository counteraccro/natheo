import { describe, expect, it } from 'vitest';
import { defineComponent, h, nextTick, ref } from 'vue';
import { mount } from '@vue/test-utils';
import { useRovingToolbar } from '@/vue/Composables/Admin/Editor/NatheoEditor/useRovingToolbar';

const Toolbar = defineComponent({
  setup() {
    const { onFocusin, onKeydown } = useRovingToolbar();
    const count = ref(3);
    return { onFocusin, onKeydown, count };
  },
  render() {
    return h(
      'div',
      { ref: 'toolbar', role: 'toolbar', onFocusin: this.onFocusin, onKeydown: this.onKeydown },
      Array.from({ length: this.count }, (_, index) => h('button', { type: 'button' }, `B${index}`))
    );
  },
});

const tabIndexes = (wrapper: ReturnType<typeof mount>): number[] =>
  wrapper.findAll('button').map((button) => (button.element as HTMLElement).tabIndex);

describe('useRovingToolbar', () => {
  it('garde un seul bouton atteignable par Tab', () => {
    const wrapper = mount(Toolbar, { attachTo: document.body });
    expect(tabIndexes(wrapper)).toEqual([0, -1, -1]);
    wrapper.unmount();
  });

  it('déplace le focus avec les flèches, Début et Fin', async () => {
    const wrapper = mount(Toolbar, { attachTo: document.body });
    const buttons = wrapper.findAll('button');
    (buttons[0].element as HTMLElement).focus();
    await wrapper.get('[role="toolbar"]').trigger('keydown', { key: 'ArrowRight' });
    expect(document.activeElement).toBe(buttons[1].element);
    expect(tabIndexes(wrapper)).toEqual([-1, 0, -1]);
    await wrapper.get('[role="toolbar"]').trigger('keydown', { key: 'ArrowLeft' });
    await wrapper.get('[role="toolbar"]').trigger('keydown', { key: 'ArrowLeft' });
    expect(document.activeElement).toBe(buttons[2].element);
    await wrapper.get('[role="toolbar"]').trigger('keydown', { key: 'Home' });
    expect(document.activeElement).toBe(buttons[0].element);
    wrapper.unmount();
  });

  it('garde le bouton courant quand un bouton est ajouté', async () => {
    const wrapper = mount(Toolbar, { attachTo: document.body });
    (wrapper.findAll('button')[1].element as HTMLElement).focus();
    (wrapper.vm as unknown as { count: number }).count = 4;
    await nextTick();
    expect(tabIndexes(wrapper)).toEqual([-1, 0, -1, -1]);
    wrapper.unmount();
  });
});
