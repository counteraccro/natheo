/*!
 * Author : Gourdon Aymeric
 * Version : 1.0
 * Point d'entrée du catalogue des composants du design system (environnement dev)
 */
// Importée ici et non dans le CSS : Tailwind ne réécrit pas les chemins des fichiers de police
import '@fontsource-variable/figtree';
import './styles/app_admin_app.css';
import { startVueControllers } from './bootstrap';

startVueControllers(import.meta.glob('./vue/controllers/Admin/DesignSystem/**/*.vue'));
