/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Déclaration des écrans d'un module dans l'application du back-office (module.ts du dossier du module)
 */
import type { RouteRecordRaw } from 'vue-router';

declare module 'vue-router' {
  interface RouteMeta {
    /** Identifiant du module de l'écran : page introuvable s'il n'est pas visible */
    module?: string;
    /** Domaines de traduction chargés avant l'affichage */
    domains?: string[];
    /** Noms des routes du fil d'Ariane, de la racine à la page, lus par le menu */
    breadcrumb?: string[];
  }
}

export interface AdminModuleDefinition {
  /** Identifiant du module dans le registre (AdminModule::id), ou de l'outil de dev */
  id: string;
  /** Outil de dev hors registre : déclaré seulement quand le démarrage contient debug (APP_ENV=dev) */
  debug?: boolean;
  /** Domaines de traduction de ses écrans */
  domains?: string[];
  routes: RouteRecordRaw[];
}

/**
 * Déclare un module, avec typage
 * @param definition
 */
export function defineAdminModule(definition: AdminModuleDefinition): AdminModuleDefinition {
  return definition;
}

/**
 * Modules du back-office et de assets/overwrite/ : un module de surcharge remplace celui du même identifiant
 * @param core modules du back-office
 * @param overwrites modules de surcharge
 */
export function mergeModules(
  core: AdminModuleDefinition[],
  overwrites: AdminModuleDefinition[]
): AdminModuleDefinition[] {
  const ids = new Set(overwrites.map((definition) => definition.id));
  return [...core.filter((definition) => !ids.has(definition.id)), ...overwrites];
}

/**
 * Routes des modules, chacune marquée de son module et de ses domaines ; outils de dev écartés hors dev
 * @param definitions
 * @param debug environnement dev
 */
export function routesOf(definitions: AdminModuleDefinition[], debug: boolean): RouteRecordRaw[] {
  return definitions.flatMap((definition) =>
    definition.debug && !debug
      ? []
      : definition.routes.map((route) => ({
          ...route,
          meta: {
            ...route.meta,
            module: definition.debug ? undefined : definition.id,
            domains: [...(definition.domains ?? []), ...(route.meta?.domains ?? [])],
          },
        }))
  );
}
