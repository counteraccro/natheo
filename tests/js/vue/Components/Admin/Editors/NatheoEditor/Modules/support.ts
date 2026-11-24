import { Editor } from '@tiptap/core';
import { Markdown } from '@tiptap/markdown';
import { modulesForPreset, natheoEditorModules } from '@/vue/Composables/Admin/Editor/NatheoEditor/natheoEditorModules';
import { markdownOf } from '@/vue/Composables/Admin/Editor/NatheoEditor/useNatheoEditor';
import { editorProps } from '@tests/vue/Composables/Admin/Editor/NatheoEditor/support';
import type { AdminEditorProps } from '@/ts/Admin/Editor/AdminEditor.type';
import type { NatheoEditorCommand } from '@/ts/Admin/Editor/NatheoEditor/NatheoEditor.type';

/**
 * Éditeur sans affichage avec les modules du cœur
 * @param markdown texte lu
 * @param overrides props du contrat
 */
export function coreEditor(markdown: string, overrides: Partial<AdminEditorProps> = {}): Editor {
  const props = editorProps({ modelValue: markdown, ...overrides });
  const modules = modulesForPreset(natheoEditorModules(), props.preset);
  return new Editor({
    element: document.createElement('div'),
    extensions: [Markdown, ...modules.flatMap((module) => module.extensions({ props }))],
    content: markdown,
    contentType: 'markdown',
  });
}

/**
 * Markdown relu puis réécrit par les modules du cœur
 * @param markdown
 */
export const rewrite = (markdown: string): string => markdownOf(coreEditor(markdown), natheoEditorModules());

/**
 * Commande d'un module du cœur
 * @param id
 * @param overrides props du contrat
 */
export function command(id: string, overrides: Partial<AdminEditorProps> = {}): NatheoEditorCommand {
  const props = editorProps(overrides);
  const found = natheoEditorModules()
    .flatMap((module) => (typeof module.commands === 'function' ? module.commands({ props }) : (module.commands ?? [])))
    .find((candidate) => candidate.id === id);
  if (found === undefined) throw new Error(`Commande inconnue : ${id}`);
  return found;
}
