/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Types pour le composant Pagination
 */

/** Entrée de pagination : un numéro de page ou une ellipse */
export type PaginationItem = { type: 'page'; page: number } | { type: 'ellipsis'; key: string };
