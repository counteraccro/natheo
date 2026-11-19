/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Types du placement des panneaux flottants (menus, infobulles)
 */
export type FloatingSide = 'top' | 'bottom';
export type FloatingAlign = 'start' | 'center' | 'end';
export type FloatingPlacement = `${FloatingSide}-${FloatingAlign}`;

export interface FloatingRect {
  top: number;
  left: number;
  width: number;
  height: number;
}

export interface FloatingPosition {
  top: number;
  left: number;
  side: FloatingSide;
  /** Position horizontale de la flèche dans le panneau, en pixels */
  arrowX: number;
}
