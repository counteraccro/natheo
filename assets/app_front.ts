/*!
 * Author : Gourdon Aymeric
 * Version : 2.0
 * Point d'entrée pour le JS et CSS du front Natheo Horizon
 */
import './styles/app_front_natheo_horizon.css';
import { startVueControllers } from './bootstrap';

startVueControllers(import.meta.glob('./vue/controllers/Front/**/*.vue'));
