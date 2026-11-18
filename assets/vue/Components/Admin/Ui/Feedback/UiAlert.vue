<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Message placé dans la page : succès, information, avertissement ou danger, action et fermeture facultatives
 */
import UiIcon from '@/vue/Components/Global/Ui/UiIcon.vue';
import UiButton from '../Form/UiButton.vue';
import { useUiLabels } from '@/vue/Composables/Admin/Ui/useUiLabels';
import {
  FEEDBACK_BORDER,
  FEEDBACK_ICONS,
  FEEDBACK_SOFT,
  FEEDBACK_TEXT,
  feedbackRole,
} from '@/vue/Composables/Admin/Ui/feedbackStyles';
import type { AlertVariant } from '@/ts/Admin/Ui/Feedback/UiAlert.type';

withDefaults(defineProps<{ variant?: AlertVariant; title?: string; dismissible?: boolean }>(), { variant: 'info' });
const emit = defineEmits<{ close: [] }>();
const labels = useUiLabels();
</script>

<template>
  <div
    :role="feedbackRole(variant)"
    class="flex items-start gap-3 rounded-md border py-3 pr-3 pl-3.5 text-fg"
    :class="[FEEDBACK_SOFT[variant], FEEDBACK_BORDER[variant]]"
  >
    <UiIcon :name="FEEDBACK_ICONS[variant]" class="mt-px size-5" :class="FEEDBACK_TEXT[variant]" />
    <div class="min-w-0 flex-1">
      <p v-if="title" class="m-0 leading-snug font-[650]">{{ title }}</p>
      <div class="text-sm text-fg-muted" :class="title && 'mt-0.5'"><slot /></div>
    </div>
    <div v-if="$slots.action || dismissible" class="flex shrink-0 items-center gap-1.5">
      <slot name="action" />
      <UiButton
        v-if="dismissible"
        variant="ghost"
        size="sm"
        icon="close"
        :aria-label="labels.alert.close"
        class="-my-1"
        @click="emit('close')"
      />
    </div>
  </div>
</template>
