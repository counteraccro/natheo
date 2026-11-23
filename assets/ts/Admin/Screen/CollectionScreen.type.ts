/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Description d'un écran de liste, état calculé par useCollectionScreen() et affiché par le thème
 */
import type { Ref } from 'vue';
import type { IconName } from '@/ts/Global/Ui/UiIcon.type';
import type { DataTableAction, DataTableColumn } from '@/ts/Admin/Ui/Data/UiDataTable.type';
import type { AdminCollection } from '@/vue/Composables/Admin/useAdminCollection';
import type { ScreenComponent, ScreenItem } from '@/ts/Admin/Screen/Screen.type';

/** Rendu d'une colonne : texte, nombre, date relative, pastille colorée, auteur */
export type CollectionColumnType = 'text' | 'number' | 'date' | 'badge' | 'author';

export interface CollectionColumn {
  key: string;
  /** Clé de traduction */
  label: string;
  type?: CollectionColumnType;
  sortable?: boolean;
  /** Pastille : champ qui porte la couleur #rrggbb */
  color?: string;
  hidden?: boolean;
  hideBelow?: 'md' | 'lg';
  /** Composant du module, reçoit { row, value } */
  component?: ScreenComponent;
}

/** Filtres génériques : statut (status) et éléments de l'utilisateur (author=me) */
export type CollectionFilter = 'status' | 'author';

export type CollectionRowAction =
  | { type: 'edit'; route: string; operation: string }
  | { type: 'toggle'; operation: string }
  | { type: 'delete'; operation: string };

export interface CollectionBulkAction {
  type: 'disable' | 'enable' | 'delete';
  operation: string;
}

/** Textes propres au module, clés de traduction (paramètre label, count) */
export interface CollectionMessages {
  /** Désactivation d'un élément, {label} */
  confirmDisable: string;
  /** Suppression d'un élément, {label} */
  confirmDelete: string;
  /** Notification après désactivation, {label} */
  disabled: string;
  /** Notification après activation, {label} */
  enabled: string;
  /** Notification après suppression, {label} */
  deleted: string;
}

export interface CollectionScreenDescriptor<T extends ScreenItem = ScreenItem> {
  /** Chemin de la collection (/admin/tags) */
  resource: string;
  title: string;
  description?: string;
  icon?: IconName;
  /** Opération de lecture de la liste : page introuvable sans elle */
  read?: { operation: string };
  columns: CollectionColumn[];
  filters?: CollectionFilter[];
  /** Route de création et opération qui la permet */
  create?: { route: string; label: string; operation: string };
  actions?: CollectionRowAction[];
  bulkActions?: CollectionBulkAction[];
  /** Libellé d'un élément dans les confirmations et notifications */
  rowLabel: (row: T) => string;
  /** État qui empêche la suppression malgré le droit (tag utilisé…) */
  deletable?: (row: T) => boolean;
  messages: CollectionMessages;
}

/** Valeur d'un filtre et son libellé traduit */
export interface ScreenOption {
  value: string;
  label: string;
}

/** Résultat d'une action groupée */
export interface BulkResult {
  done: number;
  failed: number;
}

/** État et actions de l'écran, donnés au thème */
export interface CollectionScreenState<T extends ScreenItem = ScreenItem> {
  descriptor: CollectionScreenDescriptor<T>;
  collection: AdminCollection<T>;
  /** Colonnes traduites pour UiDataTable */
  columns: DataTableColumn[];
  /** Le bouton de création est montré */
  canCreate: boolean;
  /** Adresse de l'écran de création, vide sans création */
  createHref: string;
  /** Valeurs traduites du filtre de statut, la première pour tous */
  statusOptions: ScreenOption[];
  /** Valeurs traduites du filtre d'auteur, la première pour tous */
  authorOptions: ScreenOption[];
  /** Valeur d'une cellule en texte : login de l'auteur, date et heure, valeur brute sinon */
  cellText(column: CollectionColumn, row: T): string;
  /** La requête SQL est demandée et peut être enregistrée (super administrateur) */
  canSql: boolean;
  bulkActions: DataTableAction[];
  /** Actions visibles d'une ligne, selon ses droits */
  actionsFor(row: T): DataTableAction[];
  runAction(id: string, row: T): Promise<void>;
  runBulk(id: string, keys: (string | number)[]): Promise<BulkResult | null>;
  /** Sélectionne toute la collection (plafonnée) */
  selectAll(): Promise<void>;
  /** Dernière requête enregistrée, une même requête n'est enregistrée qu'une fois */
  savedSql: Ref<string | null>;
  saveSql(sql: string): Promise<void>;
  /** Valeur d'un filtre générique, liée à la collection */
  filter: Record<CollectionFilter, Ref<string>>;
}
