/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Contrat commun aux éditeurs de texte du back-office : Markdown reçu et renvoyé, état du champ qui l'enveloppe
 */
import type { Component } from 'vue';

/** Jeu de commandes : basic (gras, italique, lien, listes), full (toutes) ou liste d'ids de modules */
export type AdminEditorPreset = 'basic' | 'full' | string[];

/** Mot-clé inséré dans le texte et remplacé à l'envoi (e-mails) */
export interface AdminEditorKeyword {
  /** Forme insérée, crochets compris : [[user.login]] */
  keyword: string;
  label: string;
}

/** Props reçues par tout éditeur ; il émet update:modelValue avec le Markdown modifié */
export interface AdminEditorProps {
  modelValue: string;
  preset: AdminEditorPreset;
  keywords: AdminEditorKeyword[];
  /** Identifiant de la zone de saisie, qui reçoit le focus depuis le récapitulatif des erreurs */
  id: string;
  /** Identifiant du libellé du champ, à poser en aria-labelledby */
  labelledBy?: string;
  /** Nom accessible quand l'éditeur est utilisé hors d'un champ */
  label?: string;
  describedBy?: string;
  invalid: boolean;
  required: boolean;
  disabled: boolean;
  readonly: boolean;
  /** Hauteur minimale en lignes */
  rows: number;
  placeholder?: string;
  /** Langue du contenu, pour le correcteur du navigateur */
  lang?: string;
  /** Identifiant du thème courant du back-office */
  theme: string;
}

/** Déclaration d'un éditeur (editor.ts de son dossier) */
export interface AdminEditor {
  id: string;
  component: Component;
}
