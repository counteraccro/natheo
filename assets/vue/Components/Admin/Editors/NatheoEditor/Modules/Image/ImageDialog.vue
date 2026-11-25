<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Fenêtre de l'image de Natheo-Editor : adresse autorisée et texte alternatif, image sélectionnée modifiée
 */
import { ref, useId } from 'vue';
import { UiButton, UiDialog, UiField, UiInput } from '@ui';
import { isAllowedImage } from '@/vue/Composables/Admin/Editor/NatheoEditor/natheoEditorUrls';
import type { NatheoEditorDialogProps } from '@/ts/Admin/Editor/NatheoEditor/NatheoEditor.type';

const props = defineProps<NatheoEditorDialogProps>();
const emit = defineEmits<{ close: [] }>();

const formId = `natheo-editor-image-${useId()}`;
const open = ref(true);
const existing = props.editor.isActive('image');
const attributes = existing ? props.editor.getAttributes('image') : {};
const src = ref<string>(String(attributes.src ?? ''));
const alt = ref<string>(String(attributes.alt ?? ''));
const error = ref<string>();

const submit = (): void => {
  const url = src.value.trim();
  if (!isAllowedImage(url)) {
    error.value = props.t('admin_ui.natheo_editor.image_dialog.error');
    return;
  }
  const image = { src: url, alt: alt.value.trim() };
  const chain = props.editor.chain().focus();
  if (existing) chain.updateAttributes('image', image);
  else chain.setImage(image);
  chain.run();
  open.value = false;
};
</script>

<template>
  <UiDialog
    v-model:open="open"
    :title="
      existing
        ? t('admin_ui.natheo_editor.image_dialog.title_edit')
        : t('admin_ui.natheo_editor.image_dialog.title_new')
    "
    @close="emit('close')"
  >
    <form :id="formId" class="grid gap-4" novalidate @submit.prevent="submit">
      <UiField :label="t('admin_ui.natheo_editor.image_dialog.url')" :error="error" required>
        <UiInput v-model="src" autofocus @update:model-value="error = undefined" />
      </UiField>
      <UiField
        :label="t('admin_ui.natheo_editor.image_dialog.alt')"
        :help="t('admin_ui.natheo_editor.image_dialog.alt_help')"
      >
        <UiInput v-model="alt" />
      </UiField>
    </form>
    <template #footer>
      <UiButton size="sm" icon="close" @click="open = false">
        {{ t('admin_ui.natheo_editor.image_dialog.cancel') }}
      </UiButton>
      <UiButton type="submit" variant="primary" size="sm" icon="image" :form="formId">
        {{
          existing
            ? t('admin_ui.natheo_editor.image_dialog.submit_edit')
            : t('admin_ui.natheo_editor.image_dialog.submit_new')
        }}
      </UiButton>
    </template>
  </UiDialog>
</template>
