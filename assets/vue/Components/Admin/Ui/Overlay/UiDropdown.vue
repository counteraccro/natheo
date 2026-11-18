<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Menu déroulant (motif « menu button ») : flèches, Début, Fin, Échap, focus rendu au bouton ; panneau rendu dans body,
 * en position fixe, rattaché à son bouton par une flèche
 */
import { nextTick, onBeforeUnmount, provide, ref, useId, watch, type ComponentPublicInstance } from 'vue';
import UiButton from '../Form/UiButton.vue';
import { useFloating } from '@/vue/Composables/Admin/Ui/useFloating';
import { DROPDOWN } from '@/vue/Composables/Admin/Ui/dropdownContext';
import type { IconName } from '@/ts/Global/Ui/UiIcon.type';
import type { ButtonSize, ButtonVariant } from '@/ts/Admin/Ui/Form/UiButton.type';
import type { FloatingPlacement } from '@/ts/Admin/Ui/Overlay/UiFloating.type';

const props = withDefaults(
  defineProps<{
    /** Texte du bouton, ou son aria-label quand iconOnly */
    label: string;
    icon?: IconName;
    iconOnly?: boolean;
    variant?: ButtonVariant;
    size?: ButtonSize;
    placement?: FloatingPlacement;
    width?: string;
  }>(),
  { variant: 'secondary', size: 'md', placement: 'bottom-end', width: 'min-w-56' }
);
const emit = defineEmits<{ open: []; close: [] }>();

const uid = useId();
const menuId = `ui-menu-${uid}`;
const triggerId = `ui-menu-button-${uid}`;
const open = ref(false);
const trigger = ref<HTMLElement | null>(null);
const panel = ref<HTMLElement | null>(null);

const items = (): HTMLElement[] =>
  panel.value ? [...panel.value.querySelectorAll<HTMLElement>('[role^="menuitem"]:not([aria-disabled="true"])')] : [];

const focusItem = (index: number): void => {
  const list = items();
  if (list.length) list[(index + list.length) % list.length].focus();
};

const show = async (focus: 'first' | 'last' | null = 'first'): Promise<void> => {
  if (!open.value) {
    open.value = true;
    emit('open');
  }
  await nextTick();
  if (focus) focusItem(focus === 'first' ? 0 : -1);
};

const close = (restoreFocus = true): void => {
  if (!open.value) return;
  open.value = false;
  emit('close');
  if (restoreFocus) trigger.value?.focus();
};

const toggle = (): void => {
  if (open.value) close();
  else show(null);
};

const { style, position } = useFloating(trigger, panel, open, props.placement, () => close(false));

const setTrigger = (el: Element | ComponentPublicInstance | null): void => {
  const element = el && '$el' in el ? el.$el : el;
  trigger.value = element instanceof HTMLElement ? element : null;
};

const onTriggerKeydown = (event: KeyboardEvent): void => {
  if (event.key === 'ArrowDown' || event.key === 'Enter' || event.key === ' ') {
    event.preventDefault();
    show('first');
  } else if (event.key === 'ArrowUp') {
    event.preventDefault();
    show('last');
  }
};

const onMenuKeydown = (event: KeyboardEvent): void => {
  const list = items();
  const index = list.indexOf(document.activeElement as HTMLElement);
  const moves: Record<string, () => void> = {
    ArrowDown: () => focusItem(index + 1),
    ArrowUp: () => focusItem(index - 1),
    Home: () => focusItem(0),
    End: () => focusItem(-1),
    Escape: () => close(),
    Tab: () => close(false),
  };
  if (moves[event.key]) {
    if (event.key !== 'Tab') event.preventDefault();
    moves[event.key]();
  }
};

const onOutside = (event: PointerEvent): void => {
  const target = event.target as Node;
  if (!panel.value?.contains(target) && !trigger.value?.contains(target)) close(false);
};

watch(open, (value) => {
  if (value) document.addEventListener('pointerdown', onOutside, true);
  else document.removeEventListener('pointerdown', onOutside, true);
});
onBeforeUnmount(() => document.removeEventListener('pointerdown', onOutside, true));

provide(DROPDOWN, { close });

defineExpose({ show, close });

const triggerProps = () => ({
  id: triggerId,
  'aria-haspopup': 'menu' as const,
  'aria-expanded': open.value,
  'aria-controls': open.value ? menuId : undefined,
  onClick: toggle,
  onKeydown: onTriggerKeydown,
});
</script>

<template>
  <slot name="trigger" :props="triggerProps()" :set-ref="setTrigger" :open="open">
    <UiButton
      :ref="setTrigger"
      v-bind="triggerProps()"
      :variant="variant"
      :size="size"
      :icon="icon"
      :aria-label="iconOnly ? label : undefined"
    >
      <template v-if="!iconOnly" #default>{{ label }}</template>
    </UiButton>
  </slot>
  <Teleport to="body">
    <div
      v-if="open"
      :id="menuId"
      ref="panel"
      role="menu"
      :aria-labelledby="triggerId"
      :data-side="position.side"
      :style="style"
      class="ui-arrow fixed z-50 rounded-md bg-raised p-1.5 text-left text-fg shadow-overlay animate-[ui-pop_.14s_var(--ease)]"
      :class="width"
      @keydown="onMenuKeydown"
    >
      <slot />
    </div>
  </Teleport>
</template>
