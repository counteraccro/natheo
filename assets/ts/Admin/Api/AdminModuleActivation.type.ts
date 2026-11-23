/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressources AdminModuleActivation et AdminOperationActivation de l'API v2 : activation des modules du back-office
 * et de leurs opérations
 */
import type { JsonLdResource } from '@/ts/Global/Api/Hydra.type';

/** Nature d'une opération, lue dans le suffixe de son nom */
export type OperationKind = 'get_collection' | 'get' | 'post' | 'put' | 'patch' | 'delete';

export interface AdminOperationActivation extends JsonLdResource {
  /** Nom de l'opération */
  id: string;
  module: string;
  method: string;
  /** Adresse sous le préfixe de l'API */
  path: string;
  kind: OperationKind;
  locked: boolean;
  /** Opération désactivée par elle-même ou par son module */
  disabled: boolean;
}

export interface AdminModuleActivation extends JsonLdResource {
  id: string;
  label: string;
  description: string;
  locked: boolean;
  disabled: boolean;
  operations: AdminOperationActivation[];
}
