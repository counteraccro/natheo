/*!
 * Author : Gourdon Aymeric
 * Version : 1.0
 * Point d'entrée pour le JS et CSS du thème Natheo-Base
 */
import './styles/app_front_natheo_base.css';
import { startVueControllers } from './bootstrap';

startVueControllers(import.meta.glob('./vue/controllers/Front/NatheoBase/**/*.vue'));
