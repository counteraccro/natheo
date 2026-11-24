/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module image de Natheo-Editor : images du texte affichées et gardées, insertion par adresse
 */
import Image from '@tiptap/extension-image';
import { defineNatheoEditorModule } from '@/vue/Composables/Admin/Editor/NatheoEditor/defineNatheoEditorModule';
import { translationKey as t } from '@/vue/Composables/Admin/Screen/defineScreen';

export default defineNatheoEditorModule({
  id: 'image',
  position: 110,
  // Dans le texte comme en Markdown, où une image est un élément du paragraphe
  extensions: () => [Image.configure({ inline: true, allowBase64: false })],
  commands: [
    {
      id: 'image',
      label: t('admin_ui.natheo_editor.command.image'),
      hint: t('admin_ui.natheo_editor.hint.image'),
      icon: 'image',
      group: 'insert',
      isActive: (editor) => editor.isActive('image'),
    },
  ],
});
