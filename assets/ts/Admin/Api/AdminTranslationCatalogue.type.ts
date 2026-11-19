/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource AdminTranslationCatalogue de l'API v2 : messages bruts (ICU) d'un domaine
 */
import type { JsonLdResource } from '@/ts/Global/Api/Hydra.type';

export interface AdminTranslationCatalogue extends JsonLdResource {
  domain: string;
  locale: string;
  messages: Record<string, string>;
}
