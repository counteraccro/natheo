import { defineComponent, h, nextTick, reactive, type PropType } from 'vue';
import { mount } from '@vue/test-utils';
import { EditorContent } from '@tiptap/vue-3';
import Document from '@tiptap/extension-document';
import Paragraph from '@tiptap/extension-paragraph';
import Text from '@tiptap/extension-text';
import Bold from '@tiptap/extension-bold';
import Italic from '@tiptap/extension-italic';
import CodeBlock from '@tiptap/extension-code-block';
import { UndoRedo } from '@tiptap/extensions';
import { defineNatheoEditorModule } from '@/vue/Composables/Admin/Editor/NatheoEditor/defineNatheoEditorModule';
import { useNatheoEditor } from '@/vue/Composables/Admin/Editor/NatheoEditor/useNatheoEditor';
import type { AdminEditorProps } from '@/ts/Admin/Editor/AdminEditor.type';
import type { NatheoEditorModule, NatheoEditorState } from '@/ts/Admin/Editor/NatheoEditor/NatheoEditor.type';

/** Modules minimaux pour éprouver le moteur */
export const testModules: NatheoEditorModule[] = [
  defineNatheoEditorModule({ id: 'document', always: true, extensions: () => [Document, Paragraph, Text] }),
  defineNatheoEditorModule({ id: 'history', extensions: () => [UndoRedo] }),
  defineNatheoEditorModule({ id: 'bold', extensions: () => [Bold] }),
  defineNatheoEditorModule({ id: 'italic', extensions: () => [Italic] }),
  defineNatheoEditorModule({ id: 'codeBlock', extensions: () => [CodeBlock] }),
];

/** Props du contrat par défaut */
export const editorProps = (overrides: Partial<AdminEditorProps> = {}): AdminEditorProps => ({
  modelValue: '',
  preset: 'full',
  keywords: [],
  id: 'champ',
  invalid: false,
  required: false,
  disabled: false,
  readonly: false,
  rows: 8,
  theme: 'natheo_studio',
  ...overrides,
});

/**
 * Monte un composant qui crée l'éditeur et affiche sa zone de saisie
 * @param overrides props du contrat
 */
export async function mountEditor(overrides: Partial<AdminEditorProps> = {}) {
  const props = reactive(editorProps(overrides));
  const emitted: string[] = [];
  let state!: NatheoEditorState;
  const Host = defineComponent({
    props: { modules: { type: Array as PropType<NatheoEditorModule[]>, required: true } },
    setup(hostProps) {
      state = useNatheoEditor(props, (value) => emitted.push(value), hostProps.modules);
      return () => h(EditorContent, { editor: state.editor.value });
    },
  });
  const wrapper = mount(Host, { props: { modules: testModules }, attachTo: document.body });
  await nextTick();
  return { wrapper, props, emitted, state, editor: () => state.editor.value };
}
