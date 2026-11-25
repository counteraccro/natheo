/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Classes communes aux rendus de Natheo-Editor : cadre selon l'état du champ, boutons du pied
 */
import type { NatheoEditorView } from '@/ts/Admin/Editor/NatheoEditor/NatheoEditor.type';

/** Bouton à bascule du pied (rendu du site, mode Markdown) */
export const FOOTER_TOGGLE_CLASSES =
  'inline-flex h-7 cursor-pointer items-center gap-1.5 rounded-[7px] border-0 bg-transparent px-2.5 text-[12.5px] font-semibold text-fg-muted hover:bg-surface hover:text-fg aria-pressed:bg-primary-soft aria-pressed:text-on-primary-soft';

/**
 * Cadre de l'éditeur : bordure du thème et anneau de focus, pointillés en lecture seule, rouge s'il est invalide
 * @param view
 * @param border classe de bordure du cadre modifiable
 */
export function editorFrameClasses(view: NatheoEditorView, border: string): (string | false)[] {
  return [
    view.editable.value
      ? `${border} bg-bg focus-within:border-primary focus-within:shadow-[0_0_0_1px_var(--primary)]`
      : 'border-dashed border-line-strong bg-surface',
    !!view.props.invalid && 'border-danger! focus-within:shadow-[0_0_0_1px_var(--danger)]!',
  ];
}
