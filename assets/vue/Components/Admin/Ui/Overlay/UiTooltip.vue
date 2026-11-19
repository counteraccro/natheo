<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Infobulle au survol (après 300 ms) et au focus de l'élément enveloppé ; Échap la ferme sans déplacer le focus,
 * le pointeur peut passer dessus sans la fermer
 */
import { onBeforeUnmount, onMounted, ref, useId } from 'vue';
import { useFloating } from '@/vue/Composables/Admin/Ui/useFloating';
import type { FloatingPlacement } from '@/ts/Admin/Ui/Overlay/UiFloating.type';

const props = withDefaults(defineProps<{ text: string; placement?: FloatingPlacement; delay?: number }>(), {
  placement: 'top-center',
  delay: 300,
});

const id = `ui-tooltip-${useId()}`;
const open = ref(false);
const wrapper = ref<HTMLElement | null>(null);
const tip = ref<HTMLElement | null>(null);
let timer: ReturnType<typeof setTimeout> | undefined;

const show = (wait: number): void => {
  clearTimeout(timer);
  timer = setTimeout(() => (open.value = true), wait);
};

const hide = (wait = 0): void => {
  clearTimeout(timer);
  timer = setTimeout(() => (open.value = false), wait);
};

const onKeydown = (event: KeyboardEvent): void => {
  if (event.key === 'Escape' && open.value) {
    event.stopPropagation();
    hide();
  }
};

const { style, position } = useFloating(wrapper, tip, open, props.placement, () => hide());

onMounted(() => {
  const target = wrapper.value?.querySelector<HTMLElement>('a, button, input, select, textarea, [tabindex]');
  if (target) {
    const current = target.getAttribute('aria-describedby');
    target.setAttribute('aria-describedby', current ? `${current} ${id}` : id);
  }
});
onBeforeUnmount(() => clearTimeout(timer));
</script>

<template>
  <span
    ref="wrapper"
    class="inline-flex"
    @mouseenter="show(delay)"
    @mouseleave="hide(100)"
    @focusin="show(0)"
    @focusout="hide()"
    @keydown="onKeydown"
  >
    <slot />
  </span>
  <Teleport to="body">
    <div
      v-show="open"
      :id="id"
      ref="tip"
      role="tooltip"
      :data-side="position.side"
      :style="style"
      class="fixed z-[70] max-w-xs rounded-sm bg-tooltip px-2.5 py-1.5 text-xs font-medium text-on-tooltip shadow-[0_4px_12px_rgb(0_0_0/.2)]"
      @mouseenter="show(0)"
      @mouseleave="hide(100)"
    >
      {{ text }}
    </div>
  </Teleport>
</template>
