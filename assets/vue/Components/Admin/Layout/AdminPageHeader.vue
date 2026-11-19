<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * En-tête d'une page : icône, titre h1, description et actions à droite
 */
import UiIcon from '@/vue/Components/Global/Ui/UiIcon.vue';
import type { IconName } from '@/ts/Global/Ui/UiIcon.type';

defineProps<{
  title: string;
  description?: string;
  /** Icône du registre, ou tracé reçu de l'API */
  icon?: IconName;
  iconPath?: string;
}>();
</script>

<template>
  <div class="flex flex-wrap items-end gap-x-6 gap-y-4">
    <div class="flex min-w-0 flex-[1_1_360px] items-center gap-4">
      <span
        v-if="icon || iconPath"
        class="grid size-12 shrink-0 place-items-center rounded-md bg-primary-soft text-on-primary-soft shadow-[inset_0_0_0_1px_color-mix(in_srgb,var(--primary)_18%,transparent)] max-md:size-10"
      >
        <UiIcon :name="icon" :path="iconPath" class="size-6" />
      </span>
      <div class="min-w-0">
        <h1 class="m-0 text-title font-bold tracking-[-0.02em] max-md:text-[22px]">{{ title }}</h1>
        <p v-if="description || $slots.description" class="m-0 mt-1 text-fg-muted">
          <slot name="description">{{ description }}</slot>
        </p>
      </div>
    </div>
    <div v-if="$slots.actions" class="flex flex-wrap gap-2">
      <slot name="actions" />
    </div>
  </div>
</template>
