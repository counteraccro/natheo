/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Consentement aux scripts des options système : choix mémorisé six mois, affichage du bandeau, activation des
 * scripts servis inertes dans des <template data-consent-script>
 */
import { readonly, shallowRef } from 'vue';
import { useFrontContext, type FrontContext } from '@/vue/Composables/Front/useFrontContext';
import type { ConsentChoice, ConsentState, StoredConsent } from '@/ts/Front/Consent.type';

const STORAGE_KEY = 'frontConsent';
const VALIDITY_MONTHS = 6;

/** Un seul état par site affiché, partagé par le bandeau et le lien du pied de page */
const states = new WeakMap<FrontContext, ConsentState>();

export function useConsent(): ConsentState {
  const context = useFrontContext();
  let state = states.get(context);
  if (state === undefined) {
    state = createConsentState(context.datas.consent !== null);
    states.set(context, state);
  }
  return state;
}

function createConsentState(required: boolean): ConsentState {
  const stored = required ? readStoredConsent() : null;
  const choice = shallowRef<ConsentChoice | null>(stored?.choice ?? null);
  const visible = shallowRef(required && stored === null);
  let activated = false;

  const activate = (): void => {
    if (!activated) {
      activated = true;
      activateScripts();
    }
  };

  const save = (value: ConsentChoice): void => {
    choice.value = value;
    visible.value = false;
    const consent: StoredConsent = { choice: value, date: new Date().toISOString() };
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(consent));
    } catch {
      // Stockage indisponible (navigation privée) : le choix vaut pour la page en cours
    }
  };

  if (choice.value === 'accepted') {
    activate();
  }

  return {
    required,
    choice: readonly(choice),
    visible: readonly(visible),
    accept(): void {
      save('accepted');
      activate();
    },
    refuse(): void {
      save('refused');
      // Un script déjà exécuté ne se retire pas : la page rechargée ne le contient plus
      if (activated) {
        window.location.reload();
      }
    },
    reopen(): void {
      visible.value = required;
    },
  };
}

/**
 * Choix enregistré s'il est lisible et date de moins de six mois, null sinon
 */
function readStoredConsent(): StoredConsent | null {
  let parsed: Partial<StoredConsent> | null;
  try {
    parsed = JSON.parse(localStorage.getItem(STORAGE_KEY) ?? 'null') as Partial<StoredConsent> | null;
  } catch {
    return null;
  }
  if (parsed === null || (parsed.choice !== 'accepted' && parsed.choice !== 'refused') || !parsed.date) {
    return null;
  }

  const date = new Date(parsed.date);
  const expiry = new Date(date);
  expiry.setMonth(expiry.getMonth() + VALIDITY_MONTHS);
  if (Number.isNaN(date.getTime()) || expiry <= new Date()) {
    return null;
  }
  return { choice: parsed.choice, date: parsed.date };
}

/**
 * Remplace chaque <template data-consent-script> par son contenu, scripts recréés pour que le navigateur les exécute
 */
function activateScripts(): void {
  for (const template of Array.from(document.querySelectorAll<HTMLTemplateElement>('template[data-consent-script]'))) {
    const fragment = template.content.cloneNode(true) as DocumentFragment;
    for (const inert of Array.from(fragment.querySelectorAll('script'))) {
      const script = document.createElement('script');
      for (const attribute of Array.from(inert.attributes)) {
        script.setAttribute(attribute.name, attribute.value);
      }
      // Scripts externes exécutés dans l'ordre de la page, comme s'ils avaient été servis directement
      script.async = inert.hasAttribute('async');
      script.text = inert.text;
      inert.replaceWith(script);
    }
    template.replaceWith(fragment);
  }
}
