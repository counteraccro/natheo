/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module heading de Natheo-Editor : paragraphe et titres 2 à 4 proposés, titres 1, 5 et 6 lus et gardés
 */
import Heading from '@tiptap/extension-heading';
import type { Level } from '@tiptap/extension-heading';
import { defineNatheoEditorModule } from '@/vue/Composables/Admin/Editor/NatheoEditor/defineNatheoEditorModule';
import { translationKey as t } from '@/vue/Composables/Admin/Screen/defineScreen';
import type { NatheoEditorCommand } from '@/ts/Admin/Editor/NatheoEditor/NatheoEditor.type';

/**
 * Commande d'un titre proposé
 * @param level
 * @param label
 * @param hint
 */
const heading = (level: Level, label: string, hint: string): NatheoEditorCommand => ({
  id: `heading${level}`,
  label,
  hint,
  markdown: '#'.repeat(level),
  icon: 'heading',
  shortcut: `Mod-Alt-${level}`,
  group: 'block',
  run: (editor) => editor.chain().focus().toggleHeading({ level }).run(),
  isActive: (editor) => editor.isActive('heading', { level }),
});

export default defineNatheoEditorModule({
  id: 'heading',
  position: 20,
  extensions: () => [Heading.configure({ levels: [1, 2, 3, 4, 5, 6] })],
  commands: [
    {
      id: 'paragraph',
      label: t('admin_ui.natheo_editor.command.paragraph'),
      hint: t('admin_ui.natheo_editor.hint.paragraph'),
      icon: 'file',
      shortcut: 'Mod-Alt-0',
      group: 'block',
      run: (editor) => editor.chain().focus().setParagraph().run(),
      isActive: (editor) => editor.isActive('paragraph'),
    },
    heading(2, t('admin_ui.natheo_editor.command.heading_2'), t('admin_ui.natheo_editor.hint.heading_2')),
    heading(3, t('admin_ui.natheo_editor.command.heading_3'), t('admin_ui.natheo_editor.hint.heading_3')),
    heading(4, t('admin_ui.natheo_editor.command.heading_4'), t('admin_ui.natheo_editor.hint.heading_4')),
  ],
});
