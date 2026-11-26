<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Dialogue de la modération groupée : commentaire de modération commun aux commentaires choisis
 */
import { ref, useId } from 'vue';
import { UiButton, UiDialog, UiField, UiIcon, UiTextarea } from '@ui';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';

/** Longueur maximale du commentaire de modération, celle de l'API */
const MAX_LENGTH = 1000;

defineProps<{ count: number; kept: number }>();
const emit = defineEmits<{ submit: [payload: Record<string, unknown>]; cancel: [] }>();

const { t } = useAdminContext();
const open = ref(true);
const reason = ref('');
const fieldId = useId();

const submit = (): void => {
  open.value = false;
  emit('submit', { moderationComment: reason.value.trim() === '' ? null : reason.value });
};
</script>

<template>
  <UiDialog
    v-model:open="open"
    :title="t('comment.dialog.moderate_title', { count })"
    tone="danger"
    @dismiss="emit('cancel')"
  >
    <template #icon>
      <span class="grid size-[34px] shrink-0 place-items-center rounded-full bg-danger-soft text-danger">
        <UiIcon name="ban" class="size-[18px]" />
      </span>
    </template>
    <p class="m-0 text-fg-muted">{{ t('comment.dialog.moderate_text', { count }) }}</p>
    <p v-if="kept > 0" class="m-0 text-sm text-fg-subtle">{{ t('comment.dialog.moderate_kept', { kept }) }}</p>
    <UiField
      :id="fieldId"
      :label="t('comment.dialog.moderate_reason')"
      :help="t('comment.dialog.moderate_help', { count })"
    >
      <UiTextarea v-model="reason" :maxlength="MAX_LENGTH" counter />
    </UiField>
    <template #footer>
      <UiButton variant="secondary" @click="emit('cancel')">{{ t('comment.dialog.moderate_cancel') }}</UiButton>
      <UiButton variant="danger" icon="ban" @click="submit">{{ t('comment.dialog.moderate_confirm') }}</UiButton>
    </template>
  </UiDialog>
</template>
