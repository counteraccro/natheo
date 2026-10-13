/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Démarre Stimulus et monte les composants Vue via vue_component() (contrôleur symfony--ux-vue--vue).
 * Chaque entrée passe son propre glob : le bundle ne contient que les composants qu'elle utilise
 */
import { Application, Controller } from '@hotwired/stimulus';
import { createApp, type Component } from 'vue';

type VueModules = Record<string, () => Promise<unknown>>;

export function startVueControllers(modules: VueModules): Application {
  const application = Application.start();

  class VueController extends Controller<HTMLElement> {
    static values = {
      component: String,
      props: Object,
    };

    declare readonly componentValue: string;
    declare readonly propsValue: Record<string, unknown>;

    async connect(): Promise<void> {
      const fullPath = `./vue/controllers/${this.componentValue}.vue`;
      const loader = modules[fullPath];

      if (!loader) {
        console.error(`Composant Vue non trouvé: ${fullPath}`);
        return;
      }

      const module = (await loader()) as { default: Component };
      createApp(module.default, this.propsValue || {}).mount(this.element);
    }
  }

  application.register('symfony--ux-vue--vue', VueController);
  return application;
}
