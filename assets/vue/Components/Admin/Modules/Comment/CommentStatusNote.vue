<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Effet du statut choisi sur le site, sous la liste des statuts du formulaire : modérateur enregistré ou effacé,
 * modérateur actuel
 */
import { computed } from 'vue';
import { UiIcon } from '@ui';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { formatDateTime } from '@/vue/Composables/Admin/Screen/screenFormat';
import type { FormScreenState } from '@/ts/Admin/Screen/FormScreen.type';
import type { AdminComment } from '@/ts/Admin/Api/AdminComment.type';

const props = defineProps<{ data: Partial<AdminComment>; screen: FormScreenState<AdminComment> }>();

const { t, bootstrap } = useAdminContext();
const moderated = computed(() => props.data.status === 'moderate');
// Le modérateur n'est rempli qu'une fois la modération enregistrée
const saved = computed(() => props.data.moderator ?? null);

const help = computed(() => {
  if (props.data.status === 'validate') return t('comment.editor.status_help_validate');
  if (moderated.value) return t('comment.editor.status_help_moderate');
  return t('comment.editor.status_help_wait_validation');
});
const change = computed(() => {
  if (moderated.value && saved.value === null) return t('comment.editor.status_help_moderator');
  if (!moderated.value && saved.value !== null) return t('comment.editor.status_help_cleared');
  return '';
});
</script>

<template>
  <div class="-mt-2 grid gap-2 text-sm text-fg-muted" aria-live="polite">
    <p class="m-0">{{ help }} {{ change }}</p>
    <p v-if="moderated && saved" class="m-0 flex items-center gap-2">
      <UiIcon name="user" class="size-4 shrink-0" />
      {{
        t('comment.editor.moderated_by', {
          login: saved.login,
          date: formatDateTime(data.updatedAt ?? null, bootstrap.locale),
        })
      }}
    </p>
  </div>
</template>
