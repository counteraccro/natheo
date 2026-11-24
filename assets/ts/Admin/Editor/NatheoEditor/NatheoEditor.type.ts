/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Types de Natheo-Editor : modules, commandes et état passé au rendu du thème
 */
import type { Component, ComputedRef, Ref, ShallowRef } from 'vue';
import type { AnyExtension, Editor } from '@tiptap/core';
import type { Editor as VueEditor } from '@tiptap/vue-3';
import type { AdminEditorProps } from '@/ts/Admin/Editor/AdminEditor.type';
import type { AdminBootstrapSiteTheme } from '@/ts/Admin/App/AdminBootstrap.type';
import type { IconName } from '@/ts/Global/Ui/UiIcon.type';

/**
 * Groupe d'une commande, dans lequel le rendu la range : heading (paragraphe et titres), block (citation, code),
 * table (commandes affichées dans un tableau)
 */
export type NatheoEditorGroup =
  'history' | 'heading' | 'block' | 'inline' | 'list' | 'insert' | 'table' | 'keyword' | 'other';

/** Commande montrée par le rendu (barre, barre flottante, menu « / ») */
export interface NatheoEditorCommand {
  id: string;
  /** Clé de traduction complète, écrite avec translationKey */
  label: string;
  /** Libellé court (sous-barre), clé de traduction */
  shortLabel?: string;
  /** Aide sous le libellé dans les menus, clé de traduction */
  hint?: string;
  /** label et hint sont déjà traduits (mots-clés donnés par le champ) */
  translated?: boolean;
  /** Syntaxe Markdown rappelée dans les menus (##) */
  markdown?: string;
  icon: IconName;
  /** Raccourci affiché dans l'infobulle (Mod-b) */
  shortcut?: string;
  group: NatheoEditorGroup;
  /** Action ; sans run, la commande ouvre la fenêtre du module */
  run?: (editor: Editor) => void;
  isActive?: (editor: Editor) => boolean;
  isEnabled?: (editor: Editor) => boolean;
  /** Commande montrée seulement selon la position du curseur */
  visible?: (editor: Editor) => boolean;
}

/** Props de la fenêtre d'un module, qui émet close */
export interface NatheoEditorDialogProps {
  editor: Editor;
  /** Commande qui l'a ouverte */
  command: string;
  t: NatheoEditorTranslate;
}

/** Ce que reçoit un module pour construire ses extensions */
export interface NatheoEditorContext {
  props: AdminEditorProps;
}

/** Module de Natheo-Editor (module.ts de son dossier) */
export interface NatheoEditorModule {
  id: string;
  /** Ordre parmi les modules, le cœur compte par 10 */
  position?: number;
  /** Chargé quel que soit le préréglage (document, HTML gardé) */
  always?: boolean;
  /** Extensions TipTap, chacune avec sa conversion Markdown */
  extensions: (context: NatheoEditorContext) => AnyExtension[];
  /** Retouche du Markdown écrit par les extensions (échappement d'une syntaxe propre) */
  transformMarkdown?: (markdown: string) => string;
  commands?: NatheoEditorCommand[] | ((context: NatheoEditorContext) => NatheoEditorCommand[]);
  /** Fenêtre ouverte par une commande sans run */
  dialog?: () => Promise<Component>;
  /** Module du back-office sans lequel le module est masqué */
  requires?: string;
}

/** État de l'éditeur, passé au rendu du thème */
export interface NatheoEditorState {
  editor: ShallowRef<VueEditor>;
  modules: NatheoEditorModule[];
  /** Nombre de mots du texte */
  words: Ref<number>;
  editable: ComputedRef<boolean>;
  /** Remplace le texte sans l'émettre, historique d'annulation repris de zéro */
  replaceContent: (value: string) => void;
  /** Mode source ouvert : la zone de texte Markdown remplace l'éditeur */
  source: Ref<boolean>;
  /** Texte du mode source, valeur émise tant qu'il est ouvert */
  sourceText: Ref<string>;
  /** Ouvre le mode source ou revient à l'éditeur, l'historique d'annulation repartant de zéro */
  toggleSource: () => void;
  setSourceText: (value: string) => void;
  /** Attributs ajoutés à la zone de saisie : classe du style du texte (natheo-editor-admin par défaut), data-front-theme */
  contentAttributes: Ref<Record<string, string>>;
  /** Hauteur de la zone de saisie : minimum de props.rows lignes, puis défilement */
  height: ComputedRef<{ minHeight: string; maxHeight: string }>;
}

/** Fenêtre de module ouverte */
export interface NatheoEditorOpenDialog {
  component: Component;
  command: string;
}

/** Traduit une clé complète */
export type NatheoEditorTranslate = (key: string, params?: Record<string, string | number>) => string;

/** Ce que reçoit le rendu du thème : état de l'éditeur, commandes et textes */
export interface NatheoEditorView extends NatheoEditorState {
  props: AdminEditorProps;
  /** Commandes de tous les modules, dans l'ordre des modules */
  commands: ComputedRef<NatheoEditorCommand[]>;
  /** Commandes d'un groupe montrées à la position du curseur */
  group: (group: NatheoEditorGroup) => NatheoEditorCommand[];
  /** Commande de bloc active (paragraphe, titre, liste…), null si aucune */
  currentBlock: () => NatheoEditorCommand | null;
  /** Lance la commande, ou ouvre la fenêtre de son module */
  run: (command: NatheoEditorCommand) => void;
  isActive: (command: NatheoEditorCommand) => boolean;
  isEnabled: (command: NatheoEditorCommand) => boolean;
  label: (command: NatheoEditorCommand) => string;
  hint: (command: NatheoEditorCommand) => string | undefined;
  shortLabel: (command: NatheoEditorCommand) => string;
  /** Raccourci lisible (⌘B, Ctrl+B), undefined sans raccourci */
  shortcut: (command: NatheoEditorCommand) => string | undefined;
  /** Libellé et raccourci, pour l'infobulle d'un bouton */
  tooltip: (command: NatheoEditorCommand) => string;
  t: NatheoEditorTranslate;
  /** Thème du site dont le texte peut être montré, null sans feuille de texte */
  siteTheme: AdminBootstrapSiteTheme | null;
  /** Texte montré comme sur le site, choix partagé par les éditeurs de l'écran */
  siteRender: Ref<boolean>;
  toggleSiteRender: () => void;
  /** Fenêtre ouverte par une commande */
  dialog: ShallowRef<NatheoEditorOpenDialog | null>;
  closeDialog: () => void;
}

/** Props du rendu de Natheo-Editor dans un thème (Themes/<Theme>/Editor/NatheoEditor.vue) */
export interface NatheoEditorRenderProps {
  view: NatheoEditorView;
}
