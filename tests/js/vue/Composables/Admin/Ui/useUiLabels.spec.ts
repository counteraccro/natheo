import { describe, expect, it } from 'vitest';
import { defineComponent, h } from 'vue';
import { mount } from '@vue/test-utils';
import { buildUiLabels, provideUiLabels, useUiLabels } from '@/vue/Composables/Admin/Ui/useUiLabels';
import catalogue from '/translations/admin_ui+intl-icu.fr.yaml?raw';

const catalogueKeys = catalogue
  .split('\n')
  .filter((line) => line.startsWith('admin_ui.'))
  // Textes de Natheo-Editor, lus par ses modules
  .filter((line) => !line.startsWith('admin_ui.natheo_editor.'))
  .map((line) => line.slice(0, line.indexOf(':')));

describe('useUiLabels', () => {
  it('lit exactement les clés du domaine admin_ui', () => {
    const used = new Set<string>();
    const labels = buildUiLabels((key) => {
      used.add(key);
      return key;
    });
    // Les messages à paramètres ne lisent leur clé qu'à l'appel
    labels.input.counter(1, 2);
    Object.values(labels).forEach((group) =>
      Object.values(group).forEach(
        (value) => typeof value === 'function' && (value as (...a: unknown[]) => string)(1, 2, 3)
      )
    );
    expect([...used].sort()).toEqual([...catalogueKeys].sort());
  });

  it('passe les paramètres au traducteur', () => {
    const labels = buildUiLabels((key, params) => `${key}:${JSON.stringify(params ?? {})}`);
    expect(labels.pagination.range(1, 20, 48)).toBe('admin_ui.pagination.range:{"from":1,"to":20,"total":48}');
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
    expect(mount(Parent).text()).toBe('admin_ui.dialog.close');
  });

  it('échoue sans fournisseur', () => {
    const Orphan = defineComponent({ setup: () => (useUiLabels(), () => null) });
    expect(() => mount(Orphan)).toThrow(/provideUiLabels/);
  });
});
