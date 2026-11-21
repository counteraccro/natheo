/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Couleur #rrggbb tirée au hasard
 */
export const randomHexColor = (): string =>
  '#' +
  Math.floor(Math.random() * 0x1000000)
    .toString(16)
    .padStart(6, '0');
