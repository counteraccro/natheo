import { describe, expect, it } from 'vitest';
import { defineComponent, type Component } from 'vue';
import { pickNatheoEditorRender } from '@/vue/Composables/Admin/Editor/NatheoEditor/natheoEditorRenders';

const render = (name: string) => {
  const component = defineComponent({ name, render: () => null });
  return { component, loader: () => Promise.resolve<Component>(component) };
};

describe('pickNatheoEditorRender', () => {
  const studio = render('Studio');
  const atelier = render('Atelier');
  const core = {
    '../Themes/NatheoStudio/Editor/NatheoEditor.vue': studio.loader,
    '../Themes/NatheoAtelier/Editor/NatheoEditor.vue': atelier.loader,
  };

  it('prend le rendu du thème', () => {
    expect(pickNatheoEditorRender('natheo_atelier', core)).toBe(atelier.loader);
  });

  it('prend celui de Natheo-Studio pour un thème sans rendu', () => {
    expect(pickNatheoEditorRender('natheo_inconnu', core)).toBe(studio.loader);
  });

  it('prend celui de la surcharge en premier', () => {
    const own = render('Own');
    expect(
      pickNatheoEditorRender('natheo_atelier', core, {
        '../overwrite/Themes/NatheoAtelier/Editor/NatheoEditor.vue': own.loader,
      })
    ).toBe(own.loader);
  });

  it('échoue sans aucun rendu', () => {
    expect(() => pickNatheoEditorRender('natheo_studio', {})).toThrow(/introuvable/);
  });
});
