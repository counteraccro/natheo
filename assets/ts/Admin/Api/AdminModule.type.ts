/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource AdminModule de l'API v2 : module du back-office visible par l'utilisateur
 */
import type { JsonLdResource } from '@/ts/Global/Api/Hydra.type';

export interface AdminModule extends JsonLdResource {
  id: string;
  label: string;
  description: string;
  locked: boolean;
  /** Noms des opérations d'API du module */
  operations: string[];
}
