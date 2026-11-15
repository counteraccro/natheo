/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Consentement du visiteur aux scripts des options système (useConsent)
 */
import type { Ref } from 'vue';

export type ConsentChoice = 'accepted' | 'refused';

/** Choix enregistré dans le stockage local du navigateur */
export interface StoredConsent {
  choice: ConsentChoice;
  /** Date du choix, ISO 8601 */
  date: string;
}

export interface ConsentState {
  /** Vrai quand le site demande le consentement (bandeau activé et au moins un script) */
  required: boolean;
  /** Choix en cours de validité, null s'il n'a pas été fait ou a expiré */
  choice: Readonly<Ref<ConsentChoice | null>>;
  /** Bandeau affiché */
  visible: Readonly<Ref<boolean>>;
  accept: () => void;
  refuse: () => void;
  /** Réaffiche le bandeau pour changer d'avis */
  reopen: () => void;
}
