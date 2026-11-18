/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Types de AdminDebugBar (environnement dev)
 */
export interface DebugSection {
  title: string;
  entries: { label: string; value: string }[];
}

export interface DebugUser {
  name: string;
  detail: string;
  initials: string;
  href: string;
  current?: boolean;
}
