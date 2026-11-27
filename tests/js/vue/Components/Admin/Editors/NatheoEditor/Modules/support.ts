import { afterEach } from 'vitest';
import { Editor } from '@tiptap/core';
import { Markdown } from '@tiptap/markdown';
import { modulesForPreset, natheoEditorModules } from '@/vue/Composables/Admin/Editor/NatheoEditor/natheoEditorModules';
import { markdownOf } from '@/vue/Composables/Admin/Editor/NatheoEditor/useNatheoEditor';
import { editorProps } from '@tests/vue/Composables/Admin/Editor/NatheoEditor/support';
import type { AdminEditorProps } from '@/ts/Admin/Editor/AdminEditor.type';
import type { NatheoEditorCommand } from '@/ts/Admin/Editor/NatheoEditor/NatheoEditor.type';

const created: Editor[] = [];

// Une vue détruite ignore la minuterie de ProseMirror qui lirait le DOM après la fin du fichier
afterEach(() => {
  created.splice(0).forEach((editor) => editor.destroy());
});

/**
 * Éditeur sans affichage avec les modules du cœur, détruit après le test
 * @param markdown texte lu
 * @param overrides props du contrat
 */
export function coreEditor(markdown: string, overrides: Partial<AdminEditorProps> = {}): Editor {
  const props = editorProps({ modelValue: markdown, ...overrides });
  const modules = modulesForPreset(natheoEditorModules(), props.preset);
  const editor = new Editor({
    element: document.createElement('div'),
    extensions: [Markdown, ...modules.flatMap((module) => module.extensions({ props }))],
    content: markdown,
    contentType: 'markdown',
  });
  created.push(editor);
  return editor;
}

/**
 * Markdown relu puis réécrit par les modules du cœur
 * @param markdown
 */
export function rewrite(markdown: string): string {
  const editor = coreEditor(markdown);
  const result = markdownOf(editor, natheoEditorModules());
  created.splice(created.indexOf(editor), 1);
  editor.destroy();
  return result;
}

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
