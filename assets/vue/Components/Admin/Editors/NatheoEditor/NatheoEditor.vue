<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Natheo-Editor : crée l'éditeur avec les modules du préréglage et l'affiche avec le rendu du thème courant
 */
import { defineAsyncComponent, inject } from 'vue';
import { routerKey } from 'vue-router';
import { ADMIN_CONTEXT } from '@/vue/Composables/Admin/useAdminContext';
import { useSiteRender } from '@/vue/Composables/Admin/Editor/NatheoEditor/siteRender';
import { modulesForPreset, natheoEditorModules } from '@/vue/Composables/Admin/Editor/NatheoEditor/natheoEditorModules';
import { useNatheoEditorView } from '@/vue/Composables/Admin/Editor/NatheoEditor/useNatheoEditorView';
import { natheoEditorRender } from '@/vue/Composables/Admin/Editor/NatheoEditor/natheoEditorRenders';
import type { AdminEditorProps } from '@/ts/Admin/Editor/AdminEditor.type';
import type { NatheoEditorTranslate } from '@/ts/Admin/Editor/NatheoEditor/NatheoEditor.type';

const props = defineProps<AdminEditorProps>();
const emit = defineEmits<{ 'update:modelValue': [value: string] }>();

const context = inject(ADMIN_CONTEXT, null);
const t: NatheoEditorTranslate = (key, params) => context?.translator.t(key, params) ?? key;
// Préréglage et modules visibles lus à la création
const modules = modulesForPreset(
  natheoEditorModules(),
  props.preset,
  (module) => context?.menu.isModuleVisible(module) ?? true
);
const view = useNatheoEditorView(props, (value) => emit('update:modelValue', value), modules, t, {
  theme: context?.bootstrap.site.theme ?? null,
  render: useSiteRender(inject(routerKey, null)),
});
const Render = defineAsyncComponent(natheoEditorRender(props.theme));
</script>

<template>
  <Render :view="view" />
  <component
    :is="view.dialog.value.component"
    v-if="view.dialog.value"
    :editor="view.editor.value"
    :command="view.dialog.value.command"
    :t="t"
    @close="view.closeDialog()"
  />
</template>
