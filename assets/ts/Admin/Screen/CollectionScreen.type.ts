/**
 * @author Gourdon Aymeric
 * @version 1.2
 * Description d'un écran de liste, état calculé par useCollectionScreen() et affiché par le thème
 */
import type { Component, Ref, ShallowRef } from 'vue';
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

/** Liste de valeurs propre au module, paramètre de l'API */
export interface CollectionSelectFilter {
  type: 'select';
  param: string;
  /** Clés de traduction du nom du filtre et de la valeur qui n'en retient aucune */
  label: string;
  allLabel: string;
  options: { value: string; label: string }[];
}

/** Paramètre posé par l'adresse (lien, composant du module), sans champ dans le thème */
export interface CollectionQueryFilter {
  type: 'query';
  param: string;
}

/**
 * Filtres : statut (status), éléments de l'utilisateur (author=me) et filtres du module ; chacun est lu dans
 * l'adresse de la liste et écrit dedans
 */
export type CollectionFilter = 'status' | 'author' | CollectionSelectFilter | CollectionQueryFilter;

export type CollectionRowAction =
  | { type: 'edit'; route: string; operation: string }
  | { type: 'toggle'; operation: string }
  | { type: 'delete'; operation: string };

/** Modification groupée propre au module : un PATCH par élément modifiable */
export interface CollectionPatchAction<T extends ScreenItem = ScreenItem> {
  type: 'patch';
  /** Identifiant de l'action, unique dans la liste */
  id: string;
  /** Clé de traduction */
  label: string;
  icon: IconName;
  operation: string;
  /** Corps envoyé, complété par celui du dialogue */
  payload?: Record<string, unknown>;
  /** Élément concerné par l'action, en plus du droit de modification (statut déjà atteint…) */
  eligible?: (row: T) => boolean;
  /** Clé de traduction du titre de la confirmation, {count} ; sans confirmation ni dialogue l'action part aussitôt */
  confirm?: string;
  /**
   * Dialogue du module, reçoit { count, kept } et émet submit (corps ajouté au payload) ou cancel ;
   * il remplace la confirmation
   */
  dialog?: ScreenComponent;
}

export type CollectionBulkAction<T extends ScreenItem = ScreenItem> =
  { type: 'disable' | 'enable' | 'delete'; operation: string } | CollectionPatchAction<T>;

/**
 * Textes propres au module, clés de traduction (paramètre label) ; ceux d'une action sont exigés quand la liste
 * la déclare (defineCollectionScreen)
 */
export interface CollectionMessages {
  /** Désactivation d'un élément, {label} */
  confirmDisable?: string;
  /** Suppression d'un élément, {label} */
  confirmDelete?: string;
  /** Notification après désactivation, {label} */
  disabled?: string;
  /** Notification après activation, {label} */
  enabled?: string;
  /** Notification après suppression, {label} */
  deleted?: string;
  /** Valeur « me » du filtre d'auteur, « Mes éléments » sans elle */
  authorMe?: string;
}

export interface CollectionScreenDescriptor<T extends ScreenItem = ScreenItem> {
  /** Chemin de la collection (/admin/tags) */
  resource: string;
  title: string;
  description?: string;
  icon?: IconName;
  /** Opération de lecture de la liste : page introuvable sans elle */
  read: { operation: string };
  columns: CollectionColumn[];
  filters?: CollectionFilter[];
  /** Route de création et opération qui la permet */
  create?: { route: string; label: string; operation: string };
  actions?: CollectionRowAction[];
  bulkActions?: CollectionBulkAction<T>[];
  /** Composant du module affiché au-dessus de la liste, reçoit { screen } */
  header?: ScreenComponent;
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

/** Filtre du module affiché par le thème, traduit, la première valeur pour tous */
export interface ScreenSelectFilter {
  param: string;
  label: string;
  options: ScreenOption[];
}

/** Dialogue d'une action groupée en attente de réponse, affiché par ScreenBulkDialog */
export interface BulkDialogRequest {
  component: Component;
  count: number;
  kept: number;
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
  /** Filtres du module avec leurs valeurs */
  selectFilters: ScreenSelectFilter[];
  /** Valeur d'une cellule en texte : login de l'auteur, date et heure, valeur brute sinon */
  cellText(column: CollectionColumn, row: T): string;
  /** La requête SQL est demandée et peut être enregistrée (super administrateur) */
  canSql: boolean;
  bulkActions: DataTableAction[];
  /** Actions visibles d'une ligne, selon ses droits */
  actionsFor(row: T): DataTableAction[];
  runAction(id: string, row: T): Promise<void>;
  runBulk(id: string, keys: (string | number)[]): Promise<BulkResult | null>;
  /** Dialogue ouvert par une action groupée, null sinon */
  bulkDialog: ShallowRef<BulkDialogRequest | null>;
  /** Réponse du dialogue : corps à envoyer, null pour annuler */
  answerBulkDialog(payload: Record<string, unknown> | null): void;
  /** Sélectionne toute la collection (plafonnée) */
  selectAll(): Promise<void>;
  /** Dernière requête enregistrée, une même requête n'est enregistrée qu'une fois */
  savedSql: Ref<string | null>;
  saveSql(sql: string): Promise<void>;
  /** Valeur de chaque filtre déclaré par son paramètre (status, author, moderation…), liée à la collection */
  filter: Record<string, Ref<string>>;
}
