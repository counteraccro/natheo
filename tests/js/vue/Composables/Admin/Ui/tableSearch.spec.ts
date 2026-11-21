import { describe, expect, it } from 'vitest';
import { filterRows, fold, highlight } from '@/vue/Composables/Admin/Ui/tableSearch';
import { tokenizeSql } from '@/vue/Composables/Admin/Ui/sqlTokens';

const columns = [
  { key: 'label', label: 'Libellé' },
  { key: 'pages', label: 'Pages' },
];
const rows = [
  { id: 1, label: 'Écologie', pages: 3 },
  { id: 2, label: 'Vue', pages: 12 },
];

describe('recherche dans la page', () => {
  it('compare sans accents ni casse en gardant la longueur', () => {
    expect(fold('ÉcoLogie')).toBe('ecologie');
    expect(fold('Œuvre').length).toBe('Œuvre'.length);
  });

  it('filtre sur les colonnes affichées', () => {
    expect(filterRows(rows, columns, 'eco').map((r) => r.id)).toEqual([1]);
    expect(filterRows(rows, columns, '12').map((r) => r.id)).toEqual([2]);
    expect(filterRows(rows, columns, '  ')).toHaveLength(2);
  });

  it('découpe le texte autour des correspondances', () => {
    expect(highlight('Écologie et économie', 'eco')).toEqual([
      { text: 'Éco', match: true },
      { text: 'logie et ', match: false },
      { text: 'éco', match: true },
      { text: 'nomie', match: false },
    ]);
  });

  it('garde les positions après un caractère sur deux unités', () => {
    expect(fold('😀 Tag')).toHaveLength('😀 Tag'.length);
    expect(highlight('😀 Tag', 'tag')).toEqual([
      { text: '😀 ', match: false },
      { text: 'Tag', match: true },
    ]);
  });
});

describe('tokenizeSql', () => {
  it('colore mots-clés, chaînes, nombres et identifiants', () => {
    const tokens = tokenizeSql("SELECT t.id FROM `tag` WHERE t.label LIKE '%vue%' LIMIT 20");
    const kinds = (kind: string) => tokens.filter((t) => t.kind === kind).map((t) => t.text);
    expect(kinds('keyword')).toEqual(['SELECT', 'FROM', 'WHERE', 'LIKE', 'LIMIT']);
    expect(kinds('string')).toEqual(["'%vue%'"]);
    expect(kinds('number')).toEqual(['20']);
    expect(kinds('identifier')).toEqual(['`tag`']);
    expect(tokens.map((t) => t.text).join('')).toBe("SELECT t.id FROM `tag` WHERE t.label LIKE '%vue%' LIMIT 20");
  });
});
