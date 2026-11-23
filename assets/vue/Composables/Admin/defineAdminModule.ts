/**
 * @author Gourdon Aymeric
 * @version 1.2
 * Déclaration des écrans d'un module dans l'application du back-office (module.ts du dossier du module)
 */
import type { RouteRecordRaw } from 'vue-router';
import type { CollectionScreenDescriptor } from '@/ts/Admin/Screen/CollectionScreen.type';
import type { FormScreenDescriptor } from '@/ts/Admin/Screen/FormScreen.type';
import type { ScreenItem } from '@/ts/Admin/Screen/Screen.type';

declare module 'vue-router' {
  interface RouteMeta {
    /** Identifiant du module de l'écran : page introuvable s'il n'est pas visible */
    module?: string;
    /** Domaines de traduction chargés avant l'affichage */
    domains?: string[];
    /** Noms des routes du fil d'Ariane, de la racine à la page, lus par le menu */
    breadcrumb?: string[];
    /** Route de la liste dont l'écran fait partie (la liste elle-même, ou le retour d'un formulaire) */
    screenGroup?: string;
    /** Opération d'API sans laquelle l'écran est introuvable */
    operation?: string;
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

/** Composant socle des écrans décrits par des données, rendus par le thème actif */
const adminScreen = () => import('@admin/App/AdminScreen.vue');

/**
 * Route d'un écran de liste décrit par des données
 * @param path
 * @param name
 * @param descriptor
 * @param breadcrumb noms des routes du fil d'Ariane
 */
export function collectionRoute<T extends ScreenItem>(
  path: string,
  name: string,
  descriptor: CollectionScreenDescriptor<T>,
  breadcrumb: string[] = [name]
): RouteRecordRaw {
  return {
    path,
    name,
    component: adminScreen,
    props: { kind: 'collection', descriptor },
    meta: { breadcrumb, screenGroup: name, operation: descriptor.read?.operation },
  };
}

/**
 * Route d'un écran de formulaire décrit par des données, création sans paramètre id (opération create, read sinon)
 * @param path
 * @param name
 * @param descriptor
 * @param breadcrumb noms des routes du fil d'Ariane
 */
export function formRoute<T extends ScreenItem>(
  path: string,
  name: string,
  descriptor: FormScreenDescriptor<T>,
  breadcrumb: string[] = [name]
): RouteRecordRaw {
  return {
    path,
    name,
    component: adminScreen,
    props: (route) => ({
      kind: 'form',
      descriptor,
      id: typeof route.params.id === 'string' ? route.params.id : null,
    }),
    meta: {
      breadcrumb,
      screenGroup: descriptor.back,
      operation: path.includes(':id') ? descriptor.read?.operation : descriptor.create?.operation,
    },
  };
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
