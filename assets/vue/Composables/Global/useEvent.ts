/**
 * Permet de créer un système d'emit globale
 * @author Gourdon Aymeric
 * @version 1.0
 */

import Emittery from 'emittery';
const emitter = new Emittery();
// Export the Emittery class and its instance.
// The `emitter` instance is more important for us here
export { emitter, Emittery };
