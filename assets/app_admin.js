/*!
 * Author : Gourdon Aymeric
 * Version : 2.0
 * Point d'entrée pour le JS et CSS Admin
 */

import { startVueControllers } from './bootstrap';

import 'flowbite';

startVueControllers(import.meta.glob(['./vue/controllers/Admin/**/*.vue', './vue/controllers/Demo.vue']));
