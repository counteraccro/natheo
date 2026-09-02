/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Tous les types TypeScript de l'éditeur markdown
 */

// ─── API exposée aux modules ─────────────────────────────────────────────────

export interface EditorApi {
  /** Insère du texte brut à la position du curseur (ou remplace la sélection) */
  insertText(text: string): void;
  /** Entoure la sélection avec un préfixe/suffixe. Si rien n'est sélectionné, insère le placeholder */
  wrapSelection(before: string, after: string, placeholder?: string): void;
  /** Préfixe la ligne courante */
  insertLinePrefix(prefix: string): void;
  /** Insère un bloc (avec sauts de ligne autour) */
  insertBlock(text: string): void;
  /** Retourne la sélection courante */
  getSelection(): EditorSelection;
  /** Focus le textarea */
  focus(): void;
  /** Retourne le markdown brut */
  getMarkdown(): string;
  /** Remplace tout le contenu */
  setMarkdown(value: string): void;
}

export interface EditorSelection {
  text: string;
  start: number;
  end: number;
}

// ─── Modules ─────────────────────────────────────────────────────────────────

export interface EditorShortcut {
  /** Touche (ex: 'b', 'i', 'k') */
  key: string;
  ctrl?: boolean;
}

export interface EditorModule {
  /** Identifiant unique */
  name: string;
  /** Texte affiché en tooltip */
  label: string;
  /** Icône SVG inline (string HTML) */
  icon: string;
  /** Raccourci clavier optionnel */
  shortcut?: EditorShortcut;
  /** Fonction exécutée au clic */
  action: (api: EditorApi) => void;
}

/** Groupes de noms de modules pour la toolbar */
export type ToolbarDefinition = string[][];

// ─── Toolbar spécifique MarkdownEditor ───────────────────────────────────────

/**
 * Noms de tous les boutons disponibles dans la toolbar du MarkdownEditor.
 *
 * Dropdowns  : 'heading' | 'keywords'
 * Formatage  : 'bold' | 'italic' | 'strikethrough' | 'blockquote'
 * Listes     : 'bulletList' | 'orderedList'
 * Blocs      : 'table' | 'code'
 * Insertion  : 'link' | 'image'
 * Action     : 'save'
 */
export type MarkdownToolbarButtonName =
  | 'heading'
  | 'keywords'
  | 'bold'
  | 'italic'
  | 'strikethrough'
  | 'blockquote'
  | 'bulletList'
  | 'orderedList'
  | 'table'
  | 'code'
  | 'link'
  | 'image'
  | 'save';

/** Toolbar du MarkdownEditor : groupes de noms de boutons */
export type MarkdownToolbar = MarkdownToolbarButtonName[][];

// ─── Props du composant ───────────────────────────────────────────────────────

export interface NatheoEditorProps {
  modelValue?: string;
  placeholder?: string;
  height?: number;
  preview?: boolean;
  emptyPreviewText?: string;
  toolbar?: ToolbarDefinition | null;
  modules?: EditorModule[];
}

// ─── Retour du composable useEditor ──────────────────────────────────────────

export interface UseEditorReturn extends EditorApi {
  textareaRef: import('vue').Ref<HTMLTextAreaElement | null>;
  markdown: import('vue').Ref<string>;
  html: import('vue').ComputedRef<string>;
  wordCount: import('vue').ComputedRef<number>;
}
