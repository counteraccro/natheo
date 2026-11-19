<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Confirmation d'une action : ton primary (confirmation) ou danger (suppression) ; « Annuler » a le focus à l'ouverture
 * d'une action destructive, la fermeture après confirmation revient à l'appelant ; Annuler, Échap et Fermer émettent
 * cancel
 */
import { computed } from 'vue';
import UiDialog from './UiDialog.vue';
import UiButton from '../Form/UiButton.vue';
import UiIcon from '@/vue/Components/Global/Ui/UiIcon.vue';
import { useUiLabels } from '@/vue/Composables/Admin/Ui/useUiLabels';
import type { IconName } from '@/ts/Global/Ui/UiIcon.type';
import type { DialogTone } from '@/ts/Admin/Ui/Overlay/UiConfirmDialog.type';

const props = withDefaults(
  defineProps<{
    title: string;
    message?: string;
    tone?: DialogTone;
    icon?: IconName;
    confirmLabel?: string;
    confirmIcon?: IconName;
    cancelLabel?: string;
    loading?: boolean;
  }>(),
  { tone: 'primary' }
);
const open = defineModel<boolean>('open', { default: false });
const emit = defineEmits<{ confirm: []; cancel: [] }>();

const labels = useUiLabels();
const danger = computed(() => props.tone === 'danger');
const iconName = computed<IconName>(() => props.icon ?? (danger.value ? 'trash' : 'info'));

const cancel = (): void => {
  open.value = false;
  emit('cancel');
};
</script>

<template>
  <UiDialog v-model:open="open" :title="title" :tone="tone" size="md" @dismiss="emit('cancel')">
    <template #icon>
      <span
        class="grid size-[34px] shrink-0 place-items-center rounded-full"
        :class="danger ? 'bg-danger-soft text-danger' : 'bg-primary-soft text-on-primary-soft'"
      >
        <UiIcon :name="iconName" class="size-[18px]" />
      </span>
    </template>
    <p v-if="message" class="m-0 text-fg-muted">{{ message }}</p>
    <slot />
    <template #footer>
      <UiButton variant="secondary" :autofocus="danger || undefined" @click="cancel">
        {{ cancelLabel ?? labels.confirmDialog.cancel }}
      </UiButton>
      <UiButton
        :variant="danger ? 'danger' : 'primary'"
        :icon="confirmIcon ?? (danger ? 'trash' : 'check')"
        :loading="loading"
        :autofocus="!danger || undefined"
        @click="emit('confirm')"
      >
        {{ confirmLabel ?? labels.confirmDialog.confirm }}
      </UiButton>
    </template>
  </UiDialog>
</template>
