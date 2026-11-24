/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Types de Natheo-Editor : modules, commandes et état passé au rendu du thème
 */
import type { Component, ComputedRef, Ref, ShallowRef } from 'vue';
import type { AnyExtension, Editor } from '@tiptap/core';
import type { Editor as VueEditor } from '@tiptap/vue-3';
import type { AdminEditorProps } from '@/ts/Admin/Editor/AdminEditor.type';
import type { IconName } from '@/ts/Global/Ui/UiIcon.type';

/** Groupe d'une commande, dans lequel le rendu la range ; table : commandes affichées dans un tableau */
export type NatheoEditorGroup = 'history' | 'block' | 'inline' | 'list' | 'insert' | 'table' | 'keyword' | 'other';

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
  /** Hauteur de la zone de saisie : minimum de props.rows lignes, puis défilement */
  height: ComputedRef<{ minHeight: string; maxHeight: string }>;
}
