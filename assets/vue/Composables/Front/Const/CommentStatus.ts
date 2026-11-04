/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Statuts des commentaires
 */
const CommentStatus = {
  waitValidation: 1,
  validate: 2,
  moderate: 3,
} as const;

export { CommentStatus };
