/*!
 * Author : Gourdon Aymeric
 * Version : 2.0
 * Point d'entrée pour le JS et CSS du front Natheo Horizon
 */
import './styles/front/natheoHorizon/global.css';
import { startVueControllers } from './bootstrap';

startVueControllers(import.meta.glob('./vue/controllers/Front/NatheoHorizon/**/*.vue'));
