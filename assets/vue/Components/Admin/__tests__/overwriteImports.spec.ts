import { describe, expect, it } from 'vitest';

const sources = {
  ...import.meta.glob<string>(
    [
      '../**/*.{vue,ts}',
      '../../../../overwrite/vue/Components/Admin/**/*.{vue,ts}',
      '!../**/__tests__/**',
      '!../../../../overwrite/**/__tests__/**',
    ],
    {
      eager: true,
      query: '?raw',
      import: 'default',
    }
  ),
  ...import.meta.glob<string>('../../../../admin_app.ts', { eager: true, query: '?raw', import: 'default' }),
};

/**
 * Imports d'un fichier du back-office qui contournent l'alias @admin/, et donc sa surcharge. Une surcharge peut
 * importer l'original par son chemin complet pour l'envelopper
 */
const bypassingImports = (source: string, isOverwrite = false): string[] =>
  [...source.matchAll(/(?:from\s+|import\()\s*'((?:\.\.?\/|@\/vue\/Components\/Admin\/)[^']*)'/g)]
    .map((match) => match[1])
    .filter((path) => !isOverwrite || !path.startsWith('@/'));

describe('Imports des composants du back-office', () => {
  it('trouve les sources', () => {
    expect(Object.keys(sources).length).toBeGreaterThan(20);
  });

  it.each(Object.entries(sources))('%s importe les composants par @admin/', (file, source) => {
    expect(bypassingImports(source, file.includes('/overwrite/'))).toEqual([]);
  });

  it('repère un import relatif ou par le chemin complet', () => {
    expect(
      bypassingImports(
        "import A from './A.vue';\nimport B from '@/vue/Components/Admin/Ui/B.vue';\nimport { C } from '@ui';\nconst D = () => import('../D.vue');"
      )
    ).toEqual(['./A.vue', '@/vue/Components/Admin/Ui/B.vue', '../D.vue']);
  });

  it("laisse une surcharge importer l'original par le chemin complet", () => {
    expect(
      bypassingImports("import A from './A.vue';\nimport B from '@/vue/Components/Admin/Ui/B.vue';", true)
    ).toEqual(['./A.vue']);
  });
});
