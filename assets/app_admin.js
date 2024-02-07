/*!
 * Author : Gourdon Aymeric
 * Version : 1.0
 * Point d'entrée pour le JS et CSS Admin
 */

// any CSS you import will output into a single css file (app.css in this case)
import 'bootstrap';
import bsCustomFileInput from 'bs-custom-file-input';


// start the Stimulus application
import './bootstrap.js';

bsCustomFileInput.init();

import { registerVueControllerComponents } from '@symfony/ux-vue';
registerVueControllerComponents(require.context('./vue/controllers', true, /\.vue$/));
//registerVueControllerComponents(require.context('./vue', true, /\.vue$/, 'lazy'));
