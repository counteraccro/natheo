import { Application, Controller } from '@hotwired/stimulus';
import { createApp } from 'vue';

// Pré-charger tous les composants Vue
const vueComponents = import.meta.glob('./vue/controllers/**/*.vue', { eager: true });

// Créer l'application Stimulus
const application = Application.start();

// Controller pour monter les composants Vue via Stimulus
class VueController extends Controller {
    static values = {
        component: String,
        props: Object
    }

    connect() {
        const componentPath = this.componentValue;
        const fullPath = `./vue/controllers/${componentPath}.vue`;

        const componentModule = vueComponents[fullPath];

        if (!componentModule) {
            console.error(`Composant Vue non trouvé: ${fullPath}`);
            return;
        }

        const component = componentModule.default;
        const app = createApp(component, this.propsValue || {});
        app.mount(this.element);
    }
}

// Enregistrer le controller avec le nom utilisé par Symfony UX
application.register('symfony--ux-vue--vue', VueController);

// Exposer Stimulus globalement pour compatibilité
window.Stimulus = application;

export { application as app };