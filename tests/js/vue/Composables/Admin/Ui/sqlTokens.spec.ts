import { describe, expect, it } from 'vitest';
import { tokenizeSql } from '@/vue/Composables/Admin/Ui/sqlTokens';

const kinds = (sql: string) => tokenizeSql(sql).filter((token) => token.kind !== 'plain');

describe('tokenizeSql', () => {
  it('rend toute la requête, morceau par morceau', () => {
    const sql = "SELECT t0_.id -- ?\nFROM `user` /* a ' b */ WHERE x = 'l''été' AND y <> -1";
    expect(
      tokenizeSql(sql)
        .map((token) => token.text)
        .join('')
    ).toBe(sql);
  });

  it('colore une requête MySQL renvoyée par l’API (antislash, accents graves)', () => {
    const sql =
      'SELECT t3_.id AS id_2 FROM tag t3_ LEFT JOIN `user` u4_ ON t3_.user_id = u4_.id ' +
      "WHERE LOWER(t0_.label) LIKE '%l\\'été ? 100\\\\% \\\\\\\\ a\\\\_b%' AND t3_.disabled = 0 ORDER BY t0_.label DESC LIMIT 20";
    const tokens = kinds(sql);
    expect(tokens.find((token) => token.kind === 'string')?.text).toBe("'%l\\'été ? 100\\\\% \\\\\\\\ a\\\\_b%'");
    expect(tokens.filter((token) => token.kind === 'identifier').map((token) => token.text)).toEqual(['`user`']);
    expect(tokens.filter((token) => token.kind === 'keyword').map((token) => token.text.toLowerCase())).toEqual([
      'select',
      'as',
      'from',
      'left',
      'join',
      'on',
      'where',
      'lower',
      'like',
      'and',
      'order',
      'by',
      'desc',
      'limit',
    ]);
  });

  it('colore une requête PostgreSQL renvoyée par l’API (apostrophe doublée, guillemets, booléens)', () => {
    const sql =
      'SELECT t3_.id FROM tag t3_ LEFT JOIN "user" u4_ ON t3_.user_id = u4_.id ' +
      "WHERE LOWER(t0_.label) LIKE '%l''été%' AND t3_.disabled = false OFFSET 20";
    const tokens = kinds(sql);
    expect(tokens.find((token) => token.kind === 'string')?.text).toBe("'%l''été%'");
    expect(tokens.find((token) => token.kind === 'identifier')?.text).toBe('"user"');
    expect(tokens.some((token) => token.kind === 'keyword' && token.text === 'false')).toBe(true);
    expect(tokens.some((token) => token.kind === 'number' && token.text === '20')).toBe(true);
  });

  it('repère les commentaires et les identifiants au guillemet doublé', () => {
    expect(kinds('SELECT 1 -- fin\n/* bloc\n*/ "a""b"').map((token) => [token.kind, token.text])).toEqual([
      ['keyword', 'SELECT'],
      ['number', '1'],
      ['comment', '-- fin'],
      ['comment', '/* bloc\n*/'],
      ['identifier', '"a""b"'],
    ]);
  });
});
