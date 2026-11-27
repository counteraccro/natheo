<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Badge d'état (point coloré facultatif) ou compteur
 */
import type { BadgeTone } from '@/ts/Admin/Ui/Display/UiBadge.type';

withDefaults(defineProps<{ tone?: BadgeTone; dot?: boolean; count?: number }>(), { tone: 'neutral', dot: true });

const TONES: Record<BadgeTone, string> = {
  neutral: 'bg-surface text-fg-muted shadow-[inset_0_0_0_1px_var(--line)]',
  primary: 'bg-primary-soft text-on-primary-soft',
  success: 'bg-success-soft text-success',
  warning: 'bg-warning-soft text-warning',
  danger: 'bg-danger-soft text-danger',
};
</script>

<template>
  <span
    v-if="count !== undefined"
    class="inline-grid h-5 min-w-5 place-items-center rounded-full px-1.5 text-2xs font-bold tabular-nums"
    :class="TONES[tone]"
  >
    {{ count }}
  </span>
  <span
    v-else
    class="inline-flex h-[22px] items-center gap-1.5 rounded-full px-2 text-xs font-semibold whitespace-nowrap"
    :class="TONES[tone]"
  >
    <span v-if="dot" class="size-1.5 rounded-full bg-current" aria-hidden="true" />
    <slot />
  </span>
</template>
