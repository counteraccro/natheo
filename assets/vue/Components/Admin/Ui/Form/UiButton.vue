<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Bouton du back-office : aplat, rendu en lien avec href ; un bouton sans texte exige un aria-label
 */
import { computed, onMounted, useAttrs, useSlots } from 'vue';
import UiIcon from '@/vue/Components/Global/Ui/UiIcon.vue';
import { useUiLabels } from '@/vue/Composables/Admin/Ui/useUiLabels';
import type { IconName } from '@/ts/Global/Ui/UiIcon.type';
import type { ButtonSize, ButtonVariant } from '@/ts/Admin/Ui/Form/UiButton.type';

const props = withDefaults(
  defineProps<{
    variant?: ButtonVariant;
    size?: ButtonSize;
    icon?: IconName;
    href?: string;
    type?: 'button' | 'submit' | 'reset';
    disabled?: boolean;
    loading?: boolean;
  }>(),
  { variant: 'secondary', size: 'md', type: 'button' }
);

const slots = useSlots();
const attrs = useAttrs();
const labels = useUiLabels();

const iconOnly = computed(() => !slots.default);
const inactive = computed(() => props.disabled || props.loading);

const VARIANTS: Record<ButtonVariant, string> = {
  primary:
    'bg-primary text-on-primary hover:bg-primary-hover aria-expanded:bg-primary-hover aria-expanded:shadow-[0_0_0_3px_color-mix(in_srgb,var(--primary)_25%,transparent)]',
  secondary:
    'border-line-strong bg-bg text-fg hover:border-field hover:bg-surface aria-expanded:border-primary aria-expanded:bg-surface aria-expanded:shadow-[0_0_0_3px_color-mix(in_srgb,var(--primary)_15%,transparent)]',
  ghost:
    'bg-transparent text-fg-muted hover:bg-surface hover:text-fg aria-expanded:bg-primary-soft aria-expanded:text-on-primary-soft aria-expanded:shadow-[inset_0_0_0_1px_color-mix(in_srgb,var(--primary)_30%,transparent)]',
  danger: 'bg-danger text-bg hover:brightness-[.93]',
  'danger-soft':
    'border-[color-mix(in_srgb,var(--danger)_35%,transparent)] bg-transparent text-danger hover:bg-danger-soft',
};

const classes = computed(() => [
  'ui-ring relative inline-flex shrink-0 cursor-pointer items-center justify-center rounded-md border border-transparent font-semibold tracking-[-0.005em] whitespace-nowrap no-underline transition-[background-color,border-color,color,box-shadow,transform] duration-100 active:translate-y-px',
  props.size === 'sm' ? 'h-8 gap-1.5 text-sm' : 'h-9 gap-[7px] text-base',
  iconOnly.value ? (props.size === 'sm' ? 'w-8' : 'w-9') : props.size === 'sm' ? 'px-3' : 'px-3.5',
  VARIANTS[props.variant],
  inactive.value && 'pointer-events-none opacity-50',
]);

const iconClass = computed(() => [
  props.size === 'sm' && !iconOnly.value ? 'size-4' : 'size-[18px]',
  props.variant === 'secondary' && 'text-fg-muted',
]);

onMounted(() => {
  if (import.meta.env.DEV && iconOnly.value && !attrs['aria-label'] && !attrs['aria-labelledby']) {
    console.warn('UiButton : un bouton sans texte exige un aria-label.');
  }
});
</script>

<template>
  <a
    v-if="href"
    :href="inactive ? undefined : href"
    :class="classes"
    :aria-disabled="inactive || undefined"
    :aria-busy="loading || undefined"
  >
    <span class="inline-flex items-center gap-[inherit]" :class="loading && 'invisible'">
      <UiIcon v-if="icon" :name="icon" :class="iconClass" />
      <slot />
    </span>
    <span
      v-if="loading"
      class="absolute size-4 animate-[ui-spin_.7s_linear_infinite] rounded-full border-2 border-current border-r-transparent"
    >
      <span class="sr-only">{{ labels.button.loading }}</span>
    </span>
  </a>
  <button v-else :type="type" :class="classes" :disabled="inactive" :aria-busy="loading || undefined">
    <span class="inline-flex items-center gap-[inherit]" :class="loading && 'invisible'">
      <UiIcon v-if="icon" :name="icon" :class="iconClass" />
      <slot />
    </span>
    <span
      v-if="loading"
      class="absolute size-4 animate-[ui-spin_.7s_linear_infinite] rounded-full border-2 border-current border-r-transparent"
    >
      <span class="sr-only">{{ labels.button.loading }}</span>
    </span>
  </button>
</template>
