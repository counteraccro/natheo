export interface OptionUpdatePayload {
  key: string;
  value: string | number;
  locale?: string;
}

export interface OptionUpdateResponse {
  success: boolean;
  msg?: string;
}

export type OptionField = HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement;

/** Champ d'une langue d'une option traduisible */
export type OptionTranslatableField = HTMLInputElement | HTMLTextAreaElement;

/** Réponse de la route d'une option de type action */
export interface OptionActionResponse {
  success: boolean;
  /** Date de la dernière action, déjà traduite */
  last?: string;
}

/** Confirmation des options de type action */
export interface OptionActionTranslate {
  title: string;
  body: string;
  cancel: string;
  confirm: string;
  error: string;
}
