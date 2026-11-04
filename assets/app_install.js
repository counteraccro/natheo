/*!
 * Author : Gourdon Aymeric
 * Version : 1.0
 * Point d'entrée pour le JS et CSS Install
 */

// Import du CSS install
import './styles/app_install.scss';

import 'flowbite';

import { startVueControllers } from './bootstrap';

startVueControllers(
  import.meta.glob(['./vue/controllers/Installation/**/*.vue', './vue/controllers/Admin/Global/ToastContainer.vue'])
);
