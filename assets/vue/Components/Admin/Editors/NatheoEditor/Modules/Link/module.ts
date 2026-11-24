/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module link de Natheo-Editor : liens vers une adresse autorisée (web, e-mail, téléphone, site, page P#id)
 */
import Link from '@tiptap/extension-link';
import { isAllowedLink } from '@/vue/Composables/Admin/Editor/NatheoEditor/natheoEditorUrls';
import { defineNatheoEditorModule } from '@/vue/Composables/Admin/Editor/NatheoEditor/defineNatheoEditorModule';
import { translationKey as t } from '@/vue/Composables/Admin/Screen/defineScreen';

export default defineNatheoEditorModule({
  id: 'link',
  position: 100,
  extensions: () => [
    Link.configure({
      openOnClick: false,
      autolink: false,
      HTMLAttributes: { target: null, rel: null },
      isAllowedUri: (url) => isAllowedLink(url),
    }),
  ],
  commands: [
    {
      id: 'link',
      label: t('admin_ui.natheo_editor.command.link'),
      hint: t('admin_ui.natheo_editor.hint.link'),
      icon: 'link',
      shortcut: 'Mod-k',
      group: 'insert',
      isActive: (editor) => editor.isActive('link'),
    },
  ],
});
