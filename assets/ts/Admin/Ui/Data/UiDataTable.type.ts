/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Types de UiDataTable
 */
import type { IconName } from '@/ts/Global/Ui/UiIcon.type';

export type DataTableRow = Record<string, unknown>;

export interface DataTableColumn {
  key: string;
  label: string;
  sortable?: boolean;
  align?: 'start' | 'end';
  /** Colonne masquée sous ce point de rupture */
  hideBelow?: 'md' | 'lg';
  /** Réglable depuis « Options » : masquée par défaut (true) ou affichée (false) */
  hidden?: boolean;
}

export interface DataTableAction {
  id: string;
  label: string;
  icon: IconName;
  danger?: boolean;
}

export interface DataTableSort {
  key: string;
  direction: 'asc' | 'desc';
}

/** État de la recherche : aucune, filtre de la page affichée, requête en base en cours, résultats de la base */
export type DataTableSearchMode = 'idle' | 'local' | 'pending' | 'remote';

/** Morceau d'un texte de cellule, surligné quand il correspond à la recherche */
export interface TextSegment {
  text: string;
  match: boolean;
}
