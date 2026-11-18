/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Recherche dans les lignes affichées d'une grille : comparaison sans accents ni casse, découpage pour le surlignage
 */
import type { DataTableColumn, DataTableRow } from '@/ts/Admin/Ui/Data/UiDataTable.type';
import type { TextSegment } from '@/ts/Admin/Ui/Data/SqlToken.type';

/**
 * Texte sans accents ni casse, de même longueur que l'original pour retrouver les positions
 */
export function fold(text: string): string {
  return [...text]
    .map((char) => {
      const base = char.normalize('NFD').replace(/\p{M}/gu, '').toLowerCase();
      return base.length === 1 ? base : char.toLowerCase().charAt(0) || char;
    })
    .join('');
}

/**
 * Texte affiché d'une cellule
 */
export function cellText(value: unknown): string {
  if (value === null || value === undefined) return '';
  return typeof value === 'object' ? '' : String(value);
}

/**
 * Lignes dont une colonne affichée contient le terme
 */
export function filterRows<T extends DataTableRow>(rows: T[], columns: DataTableColumn[], term: string): T[] {
  const needle = fold(term.trim());
  if (!needle) return rows;
  return rows.filter((row) => columns.some((column) => fold(cellText(row[column.key])).includes(needle)));
}

/**
 * Découpe un texte autour des occurrences du terme
 */
export function highlight(text: string, term: string): TextSegment[] {
  const needle = fold(term.trim());
  if (!needle) return [{ text, match: false }];
  const haystack = fold(text);
  const segments: TextSegment[] = [];
  let from = 0;
  let index = haystack.indexOf(needle);
  while (index !== -1) {
    if (index > from) segments.push({ text: text.slice(from, index), match: false });
    segments.push({ text: text.slice(index, index + needle.length), match: true });
    from = index + needle.length;
    index = haystack.indexOf(needle, from);
  }
  if (from < text.length) segments.push({ text: text.slice(from), match: false });
  return segments;
}
