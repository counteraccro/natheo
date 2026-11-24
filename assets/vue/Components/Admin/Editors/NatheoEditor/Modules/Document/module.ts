/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module document de Natheo-Editor : document, paragraphe, texte et saut de ligne forcé (\ en fin de ligne)
 */
import Document from '@tiptap/extension-document';
import Paragraph from '@tiptap/extension-paragraph';
import Text from '@tiptap/extension-text';
import HardBreak from '@tiptap/extension-hard-break';
import { defineNatheoEditorModule } from '@/vue/Composables/Admin/Editor/NatheoEditor/defineNatheoEditorModule';

export default defineNatheoEditorModule({
  id: 'document',
  position: 0,
  always: true,
  extensions: () => [Document, Paragraph, Text, HardBreak.extend({ renderMarkdown: () => '\\\n' })],
});
