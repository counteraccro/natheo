import { defineComponent, h, nextTick, reactive, ref, type Component, type PropType, type Ref } from 'vue';
import { mount } from '@vue/test-utils';
import { modulesForPreset, natheoEditorModules } from '@/vue/Composables/Admin/Editor/NatheoEditor/natheoEditorModules';
import { useNatheoEditorView } from '@/vue/Composables/Admin/Editor/NatheoEditor/useNatheoEditorView';
import { editorProps } from '@tests/vue/Composables/Admin/Editor/NatheoEditor/support';
import { labels } from '@tests/vue/Components/Admin/Ui/support';
import { UI_LABELS } from '@/vue/Composables/Admin/Ui/useUiLabels';
import type { AdminEditorProps } from '@/ts/Admin/Editor/AdminEditor.type';
import type { AdminBootstrapSiteTheme } from '@/ts/Admin/App/AdminBootstrap.type';
import type { NatheoEditorModule, NatheoEditorView } from '@/ts/Admin/Editor/NatheoEditor/NatheoEditor.type';

/** Thème du site de test */
export const siteTheme: AdminBootstrapSiteTheme = {
  id: 'natheo_horizon',
  label: 'Natheo-Horizon',
  textClass: 'nh-prose',
  stylesheet: '/build/front_natheo_horizon_content.css',
};

/** Traduction de test : la clé sans son préfixe, suivie de ses paramètres */
export const translate = (key: string, params?: Record<string, string | number>): string => {
  const short = key.replace(/^admin_ui\.natheo_editor\./, '');
  return params ? `${short}(${Object.values(params).join(',')})` : short;
};

/**
 * Monte un rendu de Natheo-Editor (ou une zone de saisie seule) avec la vue créée depuis les modules du cœur
 * @param render rendu du thème, null pour la zone de saisie seule
 * @param overrides props du contrat
 * @param modules modules, ceux du préréglage sinon
 * @param site thème du site et choix du rendu du site
 */
export async function mountView(
  render: Component | null,
  overrides: Partial<AdminEditorProps> = {},
  modules?: NatheoEditorModule[],
  site: { theme: AdminBootstrapSiteTheme | null; render: Ref<boolean> } = { theme: null, render: ref(false) }
) {
  const props = reactive(editorProps(overrides));
  const emitted: string[] = [];
  let view!: NatheoEditorView;
  const Host = defineComponent({
    props: { modules: { type: Array as PropType<NatheoEditorModule[]>, required: true } },
    setup(hostProps) {
      view = useNatheoEditorView(props, (value) => emitted.push(value), hostProps.modules, translate, site);
      return () => (render ? h(render, { view }) : h('div'));
    },
  });
  const wrapper = mount(Host, {
    props: { modules: modules ?? modulesForPreset(natheoEditorModules(), props.preset) },
    attachTo: document.body,
    global: { provide: { [UI_LABELS as symbol]: labels } },
  });
  await nextTick();
  return { wrapper, props, emitted, view, editor: () => view.editor.value };
}
