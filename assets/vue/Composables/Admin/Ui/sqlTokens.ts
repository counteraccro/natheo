/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Découpage d'une requête SQL pour sa coloration, rendue en texte sans v-html
 */
import type { SqlToken } from '@/ts/Admin/Ui/Data/SqlToken.type';

const KEYWORDS = new Set(
  (
    'select from where and or not in is null like ilike order by group having limit offset join left right inner ' +
    'outer cross full on using as distinct count asc desc nulls first last between exists union all case when then ' +
    'else end insert into values update set delete with true false escape cast coalesce lower upper'
  ).split(' ')
);

/**
 * Commentaire ; chaîne (apostrophe doublée ou échappée par un antislash) ; nombre ; identifiant entre guillemets ou
 * accents graves (guillemet doublé) ; mot ; reste
 */
const PATTERN =
  /(--[^\n]*|\/\*[\s\S]*?(?:\*\/|$))|('(?:[^'\\]|\\.|'')*'?)|(\b\d+(?:\.\d+)?\b)|("(?:[^"]|"")*"?|`(?:[^`]|``)*`?)|([A-Za-z_][\w.]*)|(\s+|[^\sA-Za-z_'`"\d-/]+|[-/])/g;

/**
 * Morceaux colorés d'une requête
 */
export function tokenizeSql(sql: string): SqlToken[] {
  const tokens: SqlToken[] = [];
  for (const [text, comment, string, number, quoted, word] of sql.matchAll(PATTERN)) {
    let kind: SqlToken['kind'] = 'plain';
    if (comment) kind = 'comment';
    else if (string) kind = 'string';
    else if (number) kind = 'number';
    else if (quoted) kind = 'identifier';
    else if (word) kind = KEYWORDS.has(word.toLowerCase()) ? 'keyword' : 'plain';
    const last = tokens[tokens.length - 1];
    if (last && last.kind === kind && kind === 'plain') last.text += text;
    else tokens.push({ text, kind });
  }
  return tokens;
}
