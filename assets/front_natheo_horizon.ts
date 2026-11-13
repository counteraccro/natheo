/*!
 * Author : Gourdon Aymeric
 * Version : 3.0
 * Point d'entrée pour le JS et CSS du thème Natheo-Horizon
 */
import './styles/app_front_natheo_horizon.css';
import { startVueControllers } from './bootstrap';

startVueControllers(import.meta.glob('./vue/controllers/Front/NatheoHorizon/**/*.vue'));
