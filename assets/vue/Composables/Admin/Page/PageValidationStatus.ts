/**
 * @author Gourdon Aymeric
 * @version 1.0
 * États de validation d'une page
 */
const PageValidationStatus = {
  none: 0,
  pending: 1,
  refused: 2,
} as const;

export { PageValidationStatus };
