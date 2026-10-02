/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Types pour le composant Mail (édition d'un email)
 */

/**
 * Mot clé insérable dans le contenu de l'email via l'éditeur markdown
 */
export interface MailKeyWord {
  label: string;
  keyword: string;
}

/**
 * Email formaté par MailService::getMailFormat()
 */
export interface Mail {
  id: number;
  // Change avec la langue pour forcer le re-rendu de l'éditeur
  key: string;
  title: string;
  description: string;
  titleTrans: string;
  contentTrans: string;
  keyWords: Record<string, string>;
}

/**
 * Traductions renvoyées par MailTranslate
 */
export interface MailTranslate {
  listLanguage: string;
  mailContentTitle: string;
  mailContentSubtitle: string;
  titleTrans: string;
  msgEmptyTitle: string;
  link_save: string;
  link_send: string;
  msg_cant_save: string;
  msg_error: string;
}

/**
 * Réponse de MailController::loadData()
 */
export interface MailLoadDataResponse {
  translateEditor: Record<string, Record<string, string>>;
  languages: Record<string, string>;
  locale: string;
  translate: MailTranslate;
  mail: Mail;
  save_url: string;
  demo_url: string;
}

/**
 * Données envoyées pour la sauvegarde ou le test d'un email
 */
export interface MailPayload {
  locale: string;
  title: string;
  content: string;
}

/**
 * Réponse ajax standard (sauvegarde, envoi de démo)
 */
export interface MailAjaxResponse {
  success: boolean;
  msg: string;
}
