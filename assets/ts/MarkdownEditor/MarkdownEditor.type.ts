/**
 * @author Gourdon Aymeric
 * @version 3.0
 * Tous les types TypeScript de l'éditeur markdown
 */

import type { ComputedRef, Ref } from 'vue';
import type { InternalLinkTranslate } from '@/ts/MarkdownEditor/InternalLink.type';
import type { MediathequeTranslate } from '@/ts/MarkdownEditor/Mediatheque.type';

// ─── API exposée aux modules ─────────────────────────────────────────────────

export interface EditorApi {
  /** Identifiant unique de l'instance d'éditeur (cible des modales) */
  editorId: string;
  /** Insère du texte brut à la position du curseur (ou remplace la sélection) */
  insertText(text: string): void;
  /** Entoure la sélection avec un préfixe/suffixe. Si rien n'est sélectionné, insère le placeholder */
  wrapSelection(before: string, after: string, placeholder?: string): void;
  /** Préfixe (ou retire le préfixe de) chaque ligne sélectionnée */
  insertLinePrefix(prefix: string): void;
  /** Insère un bloc sur ses propres lignes */
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

export interface EditorModule {
  /** Identifiant unique */
  name: string;
  /** Texte affiché en tooltip si aucune traduction n'est trouvée */
  label: string;
  /** Clé de MarkdownEditorTranslate utilisée pour le tooltip */
  translateKey?: keyof MarkdownEditorTranslate;
  /** Icône SVG inline (string HTML) */
  icon: string;
  /** Fonction exécutée au clic */
  action: (api: EditorApi) => void;
}

// ─── Toolbar ─────────────────────────────────────────────────────────────────

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

export interface MarkdownToolbarButton {
  /** Clé de traduction du tooltip */
  translateKey: keyof MarkdownEditorTranslate;
  /** Raccourci affiché dans le tooltip */
  shortcut?: string;
  icon: string;
}

export interface MarkdownEditorKeyWord {
  label: string;
  keyword: string;
}

// ─── Données serveur ─────────────────────────────────────────────────────────

/** Réponse de la route admin_markdown_load-datas */
export interface MarkdownEditorDatas {
  media: string;
  internalLinks: string;
}

// ─── Traductions (MarkdownEditorTranslate.php) ───────────────────────────────

export interface MarkdownEditorPlaceholders {
  bold: string;
  italic: string;
  strike: string;
  code: string;
  link: string;
  image: string;
  tableColumn: string;
  tableCell: string;
}

export interface MarkdownEditorTranslate {
  btnBold: string;
  btnItalic: string;
  btnStrike: string;
  btnQuote: string;
  btnList: string;
  btnListNumber: string;
  btnTable: string;
  btnLink: string;
  btnLinkInterne: string;
  btnImage: string;
  btnCode: string;
  btnSave: string;
  btnKeyWord: string;
  btnHeading: string;
  btnMediatheque: string;
  titreH1: string;
  titreH2: string;
  titreH3: string;
  titreH4: string;
  titreH5: string;
  titreH6: string;
  preview: string;
  emptyPreview: string;
  help: string;
  msgEmptyContent: string;
  textareaPlaceholder: string;
  words: string;
  caracteres: string;
  placeholders: MarkdownEditorPlaceholders;
  modaleMediatheque: MediathequeTranslate;
  modaleInternalLink: InternalLinkTranslate;
}

// ─── Retour du composable useEditor ──────────────────────────────────────────

export interface UseEditorReturn extends Omit<EditorApi, 'editorId'> {
  textareaRef: Ref<HTMLTextAreaElement | null>;
  markdown: Ref<string>;
  html: ComputedRef<string>;
  wordCount: ComputedRef<number>;
}
