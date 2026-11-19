/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Traductions de l'application du back-office : catalogues servis par l'API, messages ICU formatés par intl-messageformat
 */
import { reactive } from 'vue';
import { IntlMessageFormat } from 'intl-messageformat';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';

export type TranslateParams = Record<string, string | number>;

export interface Translator {
  readonly locale: string;
  /** Message de la clé complète (admin_app.not_found.title), la clé elle-même si elle est absente */
  t(key: string, params?: TranslateParams): string;
  /** Charge le catalogue d'un domaine s'il est absent, une seule requête par domaine */
  loadDomain(domain: string): Promise<void>;
  hasDomain(domain: string): boolean;
}

/** Lit les messages bruts d'un domaine */
export type CatalogueLoader = (domain: string) => Promise<Record<string, string>>;

/**
 * Crée le traducteur
 * @param locale langue des messages
 * @param messages catalogues déjà connus, par domaine
 * @param loader lecture d'un catalogue absent
 */
export function createTranslator(
  locale: string,
  messages: Record<string, Record<string, string>>,
  loader: CatalogueLoader
): Translator {
  // Réactif : un écran affiché avant le chargement de son domaine se met à jour
  const catalogues = reactive<Record<string, Record<string, string>>>({ ...messages });
  const formatters = new Map<string, IntlMessageFormat>();
  const loads = new Map<string, Promise<void>>();
  const warned = new Set<string>();

  const format = (key: string, message: string, params?: TranslateParams): string => {
    let formatter = formatters.get(key);
    if (formatter === undefined) {
      try {
        formatter = new IntlMessageFormat(message, locale);
      } catch {
        return message;
      }
      formatters.set(key, formatter);
    }
    try {
      return String(formatter.format(params));
    } catch {
      // Paramètre manquant : le message brut reste lisible
      return message;
    }
  };

  return {
    locale,
    t(key: string, params?: TranslateParams): string {
      const domain = key.slice(0, key.indexOf('.'));
      const message = catalogues[domain]?.[key];
      if (message === undefined) {
        if (import.meta.env.DEV && domain in catalogues && !warned.has(key)) {
          warned.add(key);
          console.warn(`Traduction absente : ${key}`);
        }
        return key;
      }
      return format(key, message, params);
    },
    loadDomain(domain: string): Promise<void> {
      if (domain in catalogues) {
        return Promise.resolve();
      }
      if (!loads.has(domain)) {
        loads.set(
          domain,
          loader(domain).then(
            (loaded) => {
              catalogues[domain] = loaded;
            },
            (error: unknown) => {
              // Un échec n'est pas gardé : le domaine est redemandé au prochain écran
              loads.delete(domain);
              throw error;
            }
          )
        );
      }
      return loads.get(domain) as Promise<void>;
    },
    hasDomain(domain: string): boolean {
      return domain in catalogues;
    },
  };
}

/**
 * Traducteur de l'application, lu dans son contexte
 */
export function useTranslate(): Translator {
  return useAdminContext().translator;
}
