/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Déclaration des écrans d'un module décrits par des données (screens.ts du dossier du module)
 */
import type { CollectionScreenDescriptor } from '@/ts/Admin/Screen/CollectionScreen.type';
import type { FormScreenDescriptor } from '@/ts/Admin/Screen/FormScreen.type';
import type { ScreenItem } from '@/ts/Admin/Screen/Screen.type';

/**
 * Clé de traduction telle quelle. Importée sous le nom t dans screens.ts (t('tag.collection.title')), elle laisse
 * VueExtractor trouver les clés des descriptions, traduites à l'affichage
 * @param key
 */
export function translationKey(key: string): string {
  return key;
}

/**
 * Lève une erreur si un texte exigé par une action déclarée manque
 * @param screen ressource de l'écran
 * @param messages textes déclarés
 * @param required textes exigés
 */
function requireMessages(screen: string, messages: object, required: string[]): void {
  const missing = required.filter((key) => !(key in messages));
  if (missing.length > 0) {
    throw new Error(`Textes absents de l'écran « ${screen} » : ${missing.join(', ')}`);
  }
}

/**
 * Déclare un écran de liste, avec typage ; vérifie les textes des actions déclarées et l'unicité des actions groupées
 * @param descriptor
 */
export function defineCollectionScreen<T extends ScreenItem>(
  descriptor: CollectionScreenDescriptor<T>
): CollectionScreenDescriptor<T> {
  const types = [...(descriptor.actions ?? []), ...(descriptor.bulkActions ?? [])].map((action) => action.type);
  requireMessages(descriptor.resource, descriptor.messages, [
    ...(types.includes('toggle') || types.includes('disable') || types.includes('enable')
      ? ['confirmDisable', 'disabled', 'enabled']
      : []),
    ...(types.includes('delete') ? ['confirmDelete', 'deleted'] : []),
  ]);
  const ids = (descriptor.bulkActions ?? []).map((action) => (action.type === 'patch' ? action.id : action.type));
  if (new Set(ids).size !== ids.length) {
    throw new Error(`Action groupée en double dans l'écran « ${descriptor.resource} »`);
  }
  return descriptor;
}

/**
 * Déclare un écran de formulaire, avec typage ; vérifie la route et les textes de la création et de la suppression
 * @param descriptor
 */
export function defineFormScreen<T extends ScreenItem>(descriptor: FormScreenDescriptor<T>): FormScreenDescriptor<T> {
  if (descriptor.create && !descriptor.editRoute) {
    throw new Error(`Route d'édition absente de l'écran « ${descriptor.resource} »`);
  }
  requireMessages(descriptor.resource, descriptor.messages, [
    ...(descriptor.create ? ['created'] : []),
    ...(descriptor.delete ? ['confirmDelete', 'deleted'] : []),
  ]);
  return descriptor;
}
