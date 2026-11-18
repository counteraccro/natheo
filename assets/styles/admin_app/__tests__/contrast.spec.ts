import { readFileSync } from 'node:fs';
import { resolve as resolvePath } from 'node:path';
import { describe, expect, it } from 'vitest';

const css = readFileSync(resolvePath(__dirname, '../tokens.css'), 'utf8');

const THEMES = ['violet', 'blue', 'green', 'red'] as const;
const MODES = ['light', 'dark'] as const;

/** Déclarations de chaque bloc, indexées par leur sélecteur */
function parseBlocks(source: string): Map<string, Record<string, string>> {
  const blocks = new Map<string, Record<string, string>>();
  const clean = source.replace(/\/\*[\s\S]*?\*\//g, '');
  for (const match of clean.matchAll(/([^{}]+)\{([^}]*)\}/g)) {
    const selector = match[1].trim().replace(/"/g, "'");
    const vars: Record<string, string> = { ...(blocks.get(selector) ?? {}) };
    for (const decl of match[2].matchAll(/(--[\w-]+)\s*:\s*([^;]+);/g)) {
      vars[decl[1]] = decl[2].trim();
    }
    blocks.set(selector, vars);
  }
  return blocks;
}

const blocks = parseBlocks(css);

/** Variables appliquées pour un thème et un mode, dans l'ordre de la cascade */
function variables(theme: string, mode: string): Record<string, string> {
  const selectors = [':root'];
  if (mode === 'dark') selectors.push(':root.dark');
  if (theme !== 'violet') selectors.push(`:root[data-theme='${theme}']`);
  if (mode === 'dark' && theme !== 'violet') selectors.push(`:root.dark[data-theme='${theme}']`);
  return Object.assign({}, ...selectors.map((s) => blocks.get(s) ?? {}));
}

function resolve(vars: Record<string, string>, name: string): string {
  let value = vars[name];
  for (let i = 0; value?.startsWith('var('); i++) {
    value = vars[value.slice(4, -1).trim()];
    if (i > 5) break;
  }
  if (!value || !/^#[0-9a-f]{6}$/i.test(value)) {
    throw new Error(`${name} : couleur hexadécimale attendue, reçu « ${value} »`);
  }
  return value;
}

function luminance(hex: string): number {
  const [r, g, b] = [1, 3, 5].map((i) => {
    const c = parseInt(hex.slice(i, i + 2), 16) / 255;
    return c <= 0.04045 ? c / 12.92 : ((c + 0.055) / 1.055) ** 2.4;
  });
  return 0.2126 * r + 0.7152 * g + 0.0722 * b;
}

function ratio(a: string, b: string): number {
  const [l1, l2] = [luminance(a), luminance(b)].sort((x, y) => y - x);
  return (l1 + 0.05) / (l2 + 0.05);
}

/** [premier plan, fond, ratio minimal] */
const PAIRS: [string, string, number][] = [
  ...['--fg', '--fg-muted', '--fg-subtle'].flatMap((fg) =>
    ['--bg', '--canvas', '--surface', '--surface-raised'].map((bg): [string, string, number] => [fg, bg, 4.5])
  ),
  ['--primary', '--bg', 4.5],
  ['--primary', '--canvas', 4.5],
  ['--on-primary', '--primary', 4.5],
  ['--on-primary', '--primary-hover', 4.5],
  ['--on-primary-soft', '--primary-soft', 4.5],
  ['--on-side', '--side-bg', 4.5],
  ['--on-side', '--side-active', 4.5],
  ['--on-side-muted', '--side-bg', 4.5],
  ['--on-side-subtle', '--side-bg', 4.5],
  ['--primary-on-dark', '--side-bg', 4.5],
  ['--on-side-accent', '--primary-on-dark', 4.5],
  ...['danger', 'success', 'warning', 'info'].flatMap((state): [string, string, number][] => [
    [`--${state}`, `--${state}-soft`, 4.5],
    [`--${state}`, '--bg', 4.5],
  ]),
  ['--bg', '--danger', 4.5],
  ['--on-tooltip', '--tooltip-bg', 4.5],
  ['--field', '--bg', 3],
  ['--focus', '--bg', 3],
  ['--focus', '--canvas', 3],
];

describe('contrastes des jetons du back-office', () => {
  for (const theme of THEMES) {
    for (const mode of MODES) {
      it(`thème ${theme}, mode ${mode}`, () => {
        const vars = variables(theme, mode);
        const failures = PAIRS.filter(([fg, bg, min]) => ratio(resolve(vars, fg), resolve(vars, bg)) < min).map(
          ([fg, bg, min]) => `${fg} sur ${bg} : ${ratio(resolve(vars, fg), resolve(vars, bg)).toFixed(2)} < ${min}`
        );
        expect(failures).toEqual([]);
      });
    }
  }
});
