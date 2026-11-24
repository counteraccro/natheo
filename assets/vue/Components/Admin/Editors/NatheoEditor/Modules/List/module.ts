/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module list de Natheo-Editor : listes à puces (-) et numérotées
 */
import { ListKit } from '@tiptap/extension-list';
import { defineNatheoEditorModule } from '@/vue/Composables/Admin/Editor/NatheoEditor/defineNatheoEditorModule';
import { translationKey as t } from '@/vue/Composables/Admin/Screen/defineScreen';

export default defineNatheoEditorModule({
  id: 'list',
  position: 70,
  extensions: () => [ListKit.configure({ taskList: false, taskItem: false })],
  commands: [
    {
      id: 'bulletList',
      label: t('admin_ui.natheo_editor.command.bullet_list'),
      hint: t('admin_ui.natheo_editor.hint.bullet_list'),
      markdown: '-',
      icon: 'list',
      shortcut: 'Mod-Shift-8',
      group: 'list',
      run: (editor) => editor.chain().focus().toggleBulletList().run(),
      isActive: (editor) => editor.isActive('bulletList'),
    },
    {
      id: 'orderedList',
      label: t('admin_ui.natheo_editor.command.ordered_list'),
      hint: t('admin_ui.natheo_editor.hint.ordered_list'),
      markdown: '1.',
      icon: 'list-ordered',
      shortcut: 'Mod-Shift-7',
      group: 'list',
      run: (editor) => editor.chain().focus().toggleOrderedList().run(),
      isActive: (editor) => editor.isActive('orderedList'),
    },
  ],
});
