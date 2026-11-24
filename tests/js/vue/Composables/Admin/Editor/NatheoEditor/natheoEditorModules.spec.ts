import { describe, expect, it } from 'vitest';
import {
  mergeEditorModules,
  modulesForPreset,
  natheoEditorModules,
} from '@/vue/Composables/Admin/Editor/NatheoEditor/natheoEditorModules';
import type { NatheoEditorModule } from '@/ts/Admin/Editor/NatheoEditor/NatheoEditor.type';

const module = (id: string, extra: Partial<NatheoEditorModule> = {}): NatheoEditorModule => ({
  id,
  extensions: () => [],
  ...extra,
});

describe('natheoEditorModules', () => {
  it('remplace un module du cœur par celui de la surcharge et trie par position', () => {
    const merged = mergeEditorModules(
      [module('italic', { position: 20 }), module('bold', { position: 10 }), module('link', { position: 30 })],
      [module('note'), module('italic', { position: 20, requires: 'surcharge' })]
    );
    expect(merged.map((m) => m.id)).toEqual(['bold', 'italic', 'link', 'note']);
    expect(merged[1].requires).toBe('surcharge');
  });

  it('filtre par préréglage en gardant les modules toujours chargés', () => {
    const modules = [module('document', { always: true }), module('bold'), module('table'), module('history')];
    expect(modulesForPreset(modules, 'full').map((m) => m.id)).toEqual(['document', 'bold', 'table', 'history']);
    expect(modulesForPreset(modules, 'basic').map((m) => m.id)).toEqual(['document', 'bold', 'history']);
    expect(modulesForPreset(modules, ['table']).map((m) => m.id)).toEqual(['document', 'table']);
  });

  it('masque un module dont le module du back-office n’est pas visible', () => {
    const modules = [module('bold'), module('media', { requires: 'media' })];
    expect(modulesForPreset(modules, 'full', (id) => id !== 'media').map((m) => m.id)).toEqual(['bold']);
  });

  it('lit les modules déclarés sans erreur', () => {
    expect(Array.isArray(natheoEditorModules())).toBe(true);
  });
});
