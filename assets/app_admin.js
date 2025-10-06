/*!
 * Author : Gourdon Aymeric
 * Version : 1.0
 * Point d'entrée pour le JS et CSS Admin
 */

// Import Bootstrap JS
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

// Import des librairies
import bsCustomFileInput from 'bs-custom-file-input';
import Masonry from 'masonry-layout';

// Initialisation de bs-custom-file-input
bsCustomFileInput.init();

// Start the Stimulus application (qui gère aussi Vue)
import './bootstrap';
