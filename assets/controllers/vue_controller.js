import { Controller } from '@hotwired/stimulus';
import { createApp } from 'vue';

export default class extends Controller {
    static values = {
        component: String,
        props: Object
    }

    async connect() {
        const componentPath = this.componentValue;
        console.log('Montage composant Vue:', componentPath);

        try {
            // Importer le composant depuis vue/controllers/
            const module = await import(`../vue/controllers/${componentPath}.vue`);
            const component = module.default;

            const app = createApp(component, this.propsValue || {});
            app.mount(this.element);

            console.log('✅ Composant Vue monté:', componentPath);
        } catch (err) {
            console.error('❌ Erreur chargement composant:', componentPath, err);
        }
    }
}