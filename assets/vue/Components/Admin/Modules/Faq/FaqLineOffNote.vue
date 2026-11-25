<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Rappel, dans la ligne ouverte de l'arbre d'une FAQ, d'un élément non affiché sur le site, avec son bouton « Activer »
 */
import { UiButton, UiIcon } from '@ui';

defineProps<{ text: string; action?: string; busy?: boolean }>();
const emit = defineEmits<{ enable: [] }>();
</script>

<template>
  <div class="flex flex-wrap items-center gap-x-2.5 gap-y-2 rounded-md bg-warning-soft px-3 py-2.5 text-sm">
    <UiIcon name="ban" class="size-4 shrink-0 text-warning" />
    <span class="min-w-0 flex-[1_1_260px] text-fg">{{ text }}</span>
    <UiButton v-if="action" size="sm" icon="check-circle" :loading="busy" @click="emit('enable')">{{
      action
    }}</UiButton>
  </div>
</template>
