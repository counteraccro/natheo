/*!
 * Author : Gourdon Aymeric
 * Version : 1.0
 * Point d'entrée pour le JS et CSS Front
 */

import './styles/app_front.css';

// start the Stimulus application
import './bootstrap';

bsCustomFileInput.init();

import { registerVueControllerComponents } from '@symfony/ux-vue';
registerVueControllerComponents(require.context('./vue/controllers', true, /\.vue$/));
//registerVueControllerComponents(require.context('./vue', true, /\.vue$/, 'lazy'));
