<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Fenêtre du lien de Natheo-Editor : adresse autorisée et texte, lien existant modifié ou retiré
 */
import { ref, useId } from 'vue';
import { UiButton, UiDialog, UiField, UiInput } from '@ui';
import { isAllowedLink } from '@/vue/Composables/Admin/Editor/NatheoEditor/natheoEditorUrls';
import type { NatheoEditorDialogProps } from '@/ts/Admin/Editor/NatheoEditor/NatheoEditor.type';

const props = defineProps<NatheoEditorDialogProps>();
const emit = defineEmits<{ close: [] }>();

const formId = `natheo-editor-link-${useId()}`;
const open = ref(true);
const existing = props.editor.isActive('link');
if (existing) props.editor.commands.extendMarkRange('link');
const { from, to } = props.editor.state.selection;
const selected = props.editor.state.doc.textBetween(from, to, ' ');
const href = ref<string>(existing ? String(props.editor.getAttributes('link').href ?? '') : '');
const text = ref(selected);
const error = ref<string>();

const submit = (): void => {
  const url = href.value.trim();
  if (!isAllowedLink(url)) {
    error.value = props.t('admin_ui.natheo_editor.link_dialog.error');
    return;
  }
  const label = text.value.trim() === '' ? url : text.value;
  const chain = props.editor.chain().focus();
  // Texte inchangé : le lien est posé sur la sélection, qui garde sa mise en forme
  if (label === selected && from !== to) chain.setLink({ href: url });
  else
    chain.insertContentAt({ from, to }, { type: 'text', text: label, marks: [{ type: 'link', attrs: { href: url } }] });
  chain.run();
  open.value = false;
};

const remove = (): void => {
  props.editor.chain().focus().extendMarkRange('link').unsetLink().run();
  open.value = false;
};
</script>

<template>
  <UiDialog
    v-model:open="open"
    :title="
      existing ? t('admin_ui.natheo_editor.link_dialog.title_edit') : t('admin_ui.natheo_editor.link_dialog.title_new')
    "
    @close="emit('close')"
  >
    <form :id="formId" class="grid gap-4" novalidate @submit.prevent="submit">
      <UiField
        :label="t('admin_ui.natheo_editor.link_dialog.url')"
        :help="t('admin_ui.natheo_editor.link_dialog.url_help')"
        :error="error"
        required
      >
        <UiInput v-model="href" autofocus @update:model-value="error = undefined" />
      </UiField>
      <UiField :label="t('admin_ui.natheo_editor.link_dialog.text')">
        <UiInput v-model="text" />
      </UiField>
    </form>
    <template #footer>
      <UiButton v-if="existing" variant="danger-soft" size="sm" icon="trash" class="mr-auto" @click="remove">
        {{ t('admin_ui.natheo_editor.link_dialog.remove') }}
      </UiButton>
      <UiButton size="sm" icon="close" @click="open = false">
        {{ t('admin_ui.natheo_editor.link_dialog.cancel') }}
      </UiButton>
      <UiButton type="submit" variant="primary" size="sm" icon="link" :form="formId">
        {{
          existing
            ? t('admin_ui.natheo_editor.link_dialog.submit_edit')
            : t('admin_ui.natheo_editor.link_dialog.submit_new')
        }}
      </UiButton>
    </template>
  </UiDialog>
</template>
