import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';
import { describe, expect, it } from 'vitest';
import { defineComponent, h } from 'vue';
import { mount } from '@vue/test-utils';
import { buildUiLabels, provideUiLabels, useUiLabels } from '@/vue/Composables/Admin/Ui/useUiLabels';

const catalogueKeys = readFileSync(resolve(__dirname, '../../../../../../translations/admin_ui+intl-icu.fr.yaml'), 'utf8')
  .split('\n')
  .filter((line) => line.startsWith('admin_ui.'))
  .map((line) => line.slice('admin_ui.'.length, line.indexOf(':')));

describe('useUiLabels', () => {
  it('lit toutes les clés du domaine admin_ui, et seulement elles', () => {
    const used = new Set<string>();
    const labels = buildUiLabels((key) => {
      used.add(key);
      return key;
    });
    // Les messages à paramètres ne lisent leur clé qu'à l'appel
    labels.input.counter(1, 2);
    Object.values(labels).forEach((group) =>
      Object.values(group).forEach((value) => typeof value === 'function' && (value as (...a: unknown[]) => string)(1, 2, 3))
    );
    expect([...used].sort()).toEqual([...catalogueKeys].sort());
  });

  it('passe les paramètres au traducteur', () => {
    const labels = buildUiLabels((key, params) => `${key}:${JSON.stringify(params ?? {})}`);
    expect(labels.pagination.range(1, 20, 48)).toBe('pagination.range:{"from":1,"to":20,"total":48}');
  });

  it('fournit les libellés aux descendants', () => {
    const labels = buildUiLabels((key) => key);
    const Child = defineComponent({ setup: () => () => h('span', useUiLabels().dialog.close) });
    const Parent = defineComponent({
      setup() {
        provideUiLabels(labels);
        return () => h(Child);
      },
    });
    expect(mount(Parent).text()).toBe('dialog.close');
  });

  it('échoue sans fournisseur', () => {
    const Orphan = defineComponent({ setup: () => (useUiLabels(), () => null) });
    expect(() => mount(Orphan)).toThrow(/provideUiLabels/);
  });
});
