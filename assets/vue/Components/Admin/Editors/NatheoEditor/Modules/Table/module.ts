/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module table de Natheo-Editor : tableau GFM, lignes et colonnes ajoutées ou supprimées
 */
import { TableKit } from '@tiptap/extension-table';
import type { Editor } from '@tiptap/core';
import { defineNatheoEditorModule } from '@/vue/Composables/Admin/Editor/NatheoEditor/defineNatheoEditorModule';
import { translationKey as t } from '@/vue/Composables/Admin/Screen/defineScreen';

/** Curseur dans un tableau : la sous-barre est montrée */
const inTable = (editor: Editor): boolean => editor.isActive('table');

export default defineNatheoEditorModule({
  id: 'table',
  position: 120,
  extensions: () => [TableKit.configure({ table: { resizable: false } })],
  commands: [
    {
      id: 'table',
      label: t('admin_ui.natheo_editor.command.table'),
      hint: t('admin_ui.natheo_editor.hint.table'),
      icon: 'table',
      group: 'insert',
      run: (editor) => editor.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run(),
      isActive: inTable,
      isEnabled: (editor) => !inTable(editor),
    },
    {
      id: 'tableAddRow',
      label: t('admin_ui.natheo_editor.command.table_add_row'),
      shortLabel: t('admin_ui.natheo_editor.short.table_add_row'),
      icon: 'row-add',
      group: 'table',
      visible: inTable,
      run: (editor) => editor.chain().focus().addRowAfter().run(),
    },
    {
      id: 'tableAddColumn',
      label: t('admin_ui.natheo_editor.command.table_add_column'),
      shortLabel: t('admin_ui.natheo_editor.short.table_add_column'),
      icon: 'col-add',
      group: 'table',
      visible: inTable,
      run: (editor) => editor.chain().focus().addColumnAfter().run(),
    },
    {
      id: 'tableDeleteRow',
      label: t('admin_ui.natheo_editor.command.table_delete_row'),
      shortLabel: t('admin_ui.natheo_editor.short.table_delete_row'),
      icon: 'row-del',
      group: 'table',
      visible: inTable,
      run: (editor) => editor.chain().focus().deleteRow().run(),
    },
    {
      id: 'tableDeleteColumn',
      label: t('admin_ui.natheo_editor.command.table_delete_column'),
      shortLabel: t('admin_ui.natheo_editor.short.table_delete_column'),
      icon: 'col-del',
      group: 'table',
      visible: inTable,
      run: (editor) => editor.chain().focus().deleteColumn().run(),
    },
    {
      id: 'tableDelete',
      label: t('admin_ui.natheo_editor.command.table_delete'),
      shortLabel: t('admin_ui.natheo_editor.short.table_delete'),
      icon: 'trash',
      group: 'table',
      visible: inTable,
      run: (editor) => editor.chain().focus().deleteTable().run(),
    },
  ],
});
