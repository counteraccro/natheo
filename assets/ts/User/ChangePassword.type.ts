/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Types pour le composant ChangePassword
 */

export interface ChangePasswordTranslate {
  password_current: string;
  password: string;
  password_2: string;
  force: string;
  btn_submit: string;
  force_nb_character: string;
  force_majuscule: string;
  force_minuscule: string;
  force_chiffre: string;
  force_character_spe: string;
  error_password_2: string;
}

/**
 * État d'affichage d'une règle de la politique de mot de passe
 */
export interface PasswordRuleState {
  class: string;
  icon: string;
  progress: number;
}

export interface PasswordRules {
  start: string;
  end: string;
  nbCharacter: string;
  majuscule: string;
  minuscule: string;
  chiffre: string;
  special: string;
}

export interface ChangePasswordResponse {
  status: 'success' | 'error';
  msg: string;
  redirect: string | false;
}
