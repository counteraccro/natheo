/*!
 * Author : Gourdon Aymeric
 * Version : 1.0
 * Point d'entrée pour le JS et CSS Install
 */

// Import du CSS install
import './styles/app_install.scss';

// Import Bootstrap JS
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

// Import Bootstrap Icons
import 'bootstrap-icons/font/bootstrap-icons.css';

import { startVueControllers } from './bootstrap';

startVueControllers(import.meta.glob('./vue/controllers/Installation/**/*.vue'));
