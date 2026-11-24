<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Champ de texte mis en forme : charge l'éditeur actif à la demande et lui passe l'état du UiField qui l'enveloppe ;
 * zone de texte Markdown si l'éditeur ne peut pas être chargé
 */
import { computed, inject, onMounted, ref, shallowRef, type Component } from 'vue';
import UiAlert from '@admin/Ui/Feedback/UiAlert.vue';
import UiTextarea from '@admin/Ui/Form/UiTextarea.vue';
import { useFieldControl, useFieldLabel } from '@/vue/Composables/Admin/Ui/useField';
import { useUiLabels } from '@/vue/Composables/Admin/Ui/useUiLabels';
import { CONTROL_CLASSES } from '@/vue/Composables/Admin/Ui/controlClasses';
import { loadAdminEditor } from '@/vue/Composables/Admin/Editor/loadAdminEditor';
import { ADMIN_THEME } from '@/vue/Composables/Admin/Screen/adminTheme';
import { DEFAULT_ADMIN_THEME } from '@/vue/Composables/Admin/loadAdminTheme';
import type { AdminEditorKeyword, AdminEditorPreset } from '@/ts/Admin/Editor/AdminEditor.type';

const props = withDefaults(
  defineProps<{
    preset?: AdminEditorPreset;
    keywords?: AdminEditorKeyword[];
    /** Nom accessible hors d'un UiField */
    label?: string;
    disabled?: boolean;
    readonly?: boolean;
    rows?: number;
    placeholder?: string;
    lang?: string;
  }>(),
  { preset: 'full', keywords: () => [], rows: 8 }
);

const model = defineModel<string | null>();
const labels = useUiLabels();
const control = useFieldControl();
const labelledBy = useFieldLabel();
const theme = inject(ADMIN_THEME, null)?.id ?? DEFAULT_ADMIN_THEME;

const editor = shallowRef<Component | null>(null);
const failed = ref(false);

onMounted(() => {
  loadAdminEditor()
    .then((loaded) => (editor.value = loaded.component))
    .catch((error: unknown) => {
      failed.value = true;
      console.error(error);
    });
});

const editorProps = computed(() => ({
  modelValue: model.value ?? '',
  preset: props.preset,
  keywords: props.keywords,
  id: control.value.id,
  labelledBy: labelledBy.value,
  label: props.label,
  describedBy: control.value['aria-describedby'],
  invalid: control.value['aria-invalid'] === true,
  required: control.value.required === true,
  disabled: props.disabled,
  readonly: props.readonly,
  rows: props.rows,
  placeholder: props.placeholder,
  lang: props.lang,
  theme,
}));
</script>

<template>
  <component :is="editor" v-if="editor" v-bind="editorProps" @update:model-value="model = $event" />
  <template v-else-if="failed">
    <UiAlert variant="warning">{{ labels.textEditor.loadError }}</UiAlert>
    <UiTextarea
      v-model="model"
      :rows="rows"
      :disabled="disabled"
      :readonly="readonly"
      :placeholder="placeholder"
      :lang="lang"
      :aria-label="labelledBy ? undefined : label"
    />
  </template>
  <div
    v-else
    :class="CONTROL_CLASSES"
    :style="{ minHeight: `${rows * 1.65 + 1}rem` }"
    class="motion-safe:animate-pulse"
    aria-busy="true"
  >
    <span class="sr-only">{{ labels.textEditor.loading }}</span>
  </div>
</template>
