/*!
 * Author : Gourdon Aymeric
 * Version : 1.0
 * Point d'entrée pour le JS et CSS Front
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app_install.scss';

import 'bootstrap';
import 'bootstrap-icons/font/bootstrap-icons.css';


// start the Stimulus application
import './bootstrap';

import { registerVueControllerComponents } from '@symfony/ux-vue';
registerVueControllerComponents(require.context('./vue/controllers', true, /\.vue$/));
//registerVueControllerComponents(require.context('./vue', true, /\.vue$/, 'lazy'));
