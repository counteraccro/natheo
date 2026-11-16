/*!
 * Author : Gourdon Aymeric
 * Version : 1.0
 * Point d'entrée pour le JS et CSS du thème StormFront
 */
// Importées ici et non dans le CSS : Tailwind ne réécrit pas les chemins des fichiers de police
import '@fontsource-variable/geist';
import '@fontsource-variable/geist-mono';
import './styles/app_front_natheo_storm.css';
import { startVueControllers } from './bootstrap';

startVueControllers(import.meta.glob('./vue/controllers/Front/StormFront/**/*.vue'));
