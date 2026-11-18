/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Classes communes aux champs de saisie du back-office (bordure ≥ 3:1, focus, état invalide)
 */
export const CONTROL_CLASSES =
  'w-full rounded-md border border-field bg-bg text-base text-fg transition-[border-color,box-shadow] duration-100 placeholder:text-fg-subtle hover:border-fg-subtle focus:border-primary focus:shadow-[0_0_0_3px_color-mix(in_srgb,var(--primary)_18%,transparent)] focus:outline-none disabled:cursor-not-allowed disabled:opacity-60 aria-invalid:border-danger aria-invalid:shadow-[0_0_0_3px_color-mix(in_srgb,var(--danger)_14%,transparent)]';
