/*!
 * Author : Gourdon Aymeric
 * Version : 3.0
 * Point d'entrée pour le JS et CSS du thème Natheo-Horizon
 */
// Importée ici et non dans le CSS : Tailwind ne réécrit pas les chemins des fichiers de police
import '@fontsource-variable/schibsted-grotesk';
import './styles/app_front_natheo_horizon.css';
import { startVueControllers } from './bootstrap';

startVueControllers(import.meta.glob('./vue/controllers/Front/NatheoHorizon/**/*.vue'));
