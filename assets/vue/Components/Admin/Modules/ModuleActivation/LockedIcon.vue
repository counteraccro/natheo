<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Cadenas d'un module ou d'une opération verrouillé, expliqué par une info-bulle
 */
import { UiIcon, UiTooltip } from '@ui';

defineProps<{ label: string }>();
</script>

<template>
  <UiTooltip :text="label">
    <span class="inline-flex text-fg-subtle">
      <UiIcon name="lock" class="size-4" :label="label" />
    </span>
  </UiTooltip>
</template>
