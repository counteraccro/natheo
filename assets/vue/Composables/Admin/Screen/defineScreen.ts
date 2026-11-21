/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Déclaration des écrans d'un module décrits par des données (screens.ts du dossier du module)
 */
import type { CollectionScreenDescriptor } from '@/ts/Admin/Screen/CollectionScreen.type';
import type { FormScreenDescriptor } from '@/ts/Admin/Screen/FormScreen.type';
import type { ScreenItem } from '@/ts/Admin/Screen/Screen.type';

/**
 * Clé de traduction telle quelle. Importée sous le nom t dans screens.ts (t('tag.index.title')), elle laisse
 * VueExtractor trouver les clés des descriptions, traduites à l'affichage
 * @param key
 */
export function translationKey(key: string): string {
  return key;
}

/**
 * Déclare un écran de liste, avec typage
 * @param descriptor
 */
export function defineCollectionScreen<T extends ScreenItem>(
  descriptor: CollectionScreenDescriptor<T>
): CollectionScreenDescriptor<T> {
  return descriptor;
}

/**
 * Déclare un écran de formulaire, avec typage
 * @param descriptor
 */
export function defineFormScreen<T extends ScreenItem>(descriptor: FormScreenDescriptor<T>): FormScreenDescriptor<T> {
  return descriptor;
}
