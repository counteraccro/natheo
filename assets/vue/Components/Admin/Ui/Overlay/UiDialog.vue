<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Modale sur <dialog> natif : focus piégé et arrière-plan inerte par le navigateur, Échap et bouton Fermer (événement
 * dismiss), focus rendu à l'élément d'origine
 */
import { onBeforeUnmount, onMounted, ref, useId, watch } from 'vue';
import UiButton from '@admin/Ui/Form/UiButton.vue';
import { useUiLabels } from '@/vue/Composables/Admin/Ui/useUiLabels';
import type { DialogSize, DialogTone } from '@/ts/Admin/Ui/Overlay/UiConfirmDialog.type';

const props = withDefaults(defineProps<{ title: string; size?: DialogSize; tone?: DialogTone }>(), { size: 'md' });
const open = defineModel<boolean>('open', { default: false });
const emit = defineEmits<{ close: []; dismiss: [] }>();

const labels = useUiLabels();
const dialog = ref<HTMLDialogElement | null>(null);
const titleId = `ui-dialog-${useId()}`;
let opener: HTMLElement | null = null;

const WIDTHS: Record<DialogSize, string> = {
  sm: 'w-[min(400px,calc(100vw-32px))]',
  md: 'w-[min(480px,calc(100vw-32px))]',
  lg: 'w-[min(640px,calc(100vw-32px))]',
};

const sync = (value: boolean): void => {
  const element = dialog.value;
  if (!element) return;
  if (value && !element.open) {
    opener = document.activeElement instanceof HTMLElement ? document.activeElement : null;
    element.showModal();
  } else if (!value && element.open) {
    element.close();
  }
};

const onClose = (): void => {
  if (open.value) open.value = false;
  emit('close');
  opener?.focus();
  opener = null;
};

const dismiss = (): void => {
  emit('dismiss');
  open.value = false;
};

watch(open, sync, { flush: 'post' });
onMounted(() => sync(open.value));
onBeforeUnmount(() => dialog.value?.open && dialog.value.close());
</script>

<template>
  <dialog
    ref="dialog"
    :aria-labelledby="titleId"
    :data-tone="props.tone"
    class="ui-dialog m-auto max-h-[calc(100dvh-32px)] overflow-hidden rounded-lg border-0 bg-raised p-0 text-fg shadow-dialog open:animate-[ui-pop_.2s_var(--ease)]"
    :class="WIDTHS[size]"
    @cancel="emit('dismiss')"
    @close="onClose"
  >
    <div v-if="open" class="relative flex max-h-[calc(100dvh-35px)] flex-col gap-3.5 overflow-y-auto p-6">
      <header class="-mx-6 -mt-1.5 mb-0.5 flex items-center gap-3 border-b border-line pr-14 pb-3.5 pl-6">
        <slot name="icon" />
        <h2 :id="titleId" class="m-0 text-md font-[650] tracking-[-0.005em]">{{ title }}</h2>
      </header>
      <slot />
      <footer v-if="$slots.footer" class="mt-2.5 flex flex-wrap justify-end gap-2">
        <slot name="footer" />
      </footer>
      <UiButton
        variant="ghost"
        size="sm"
        icon="close"
        :aria-label="labels.dialog.close"
        class="absolute! top-3 right-3"
        @click="dismiss"
      />
    </div>
  </dialog>
</template>
