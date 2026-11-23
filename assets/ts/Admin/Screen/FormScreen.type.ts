/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Description d'un écran de formulaire, état calculé par useFormScreen() et affiché par le thème
 */
import type { Ref } from 'vue';
import type { AdminResource } from '@/vue/Composables/Admin/useAdminResource';
import type { IconName } from '@/ts/Global/Ui/UiIcon.type';
import type { ScreenComponent, ScreenItem } from '@/ts/Admin/Screen/Screen.type';
import type { SectionKind } from '@/vue/Composables/Admin/Screen/sectionKinds';
import type { ResourceChange } from '@/vue/Composables/Admin/Screen/resourceChanges';

interface BaseField {
  /** Propriété de la ressource, chemin des erreurs de l'API */
  name: string;
  /** Clé de traduction */
  label?: string;
  help?: string;
}

export type FormField =
  | (BaseField & { type: 'text' | 'textarea'; maxlength?: number })
  | (BaseField & { type: 'switch' })
  | (BaseField & { type: 'select'; options: { value: string; label: string }[] })
  | (BaseField & { type: 'color'; suggestions?: number })
  /** Un champ par langue du site dans la liste translations ({ locale, <field> }) */
  | (BaseField & { type: 'translations'; field: string; maxlength?: number; copyFromCurrent?: boolean })
  /** Composant du module, reçoit { data, screen } ; non envoyé à l'API */
  | (BaseField & { type: 'component'; component: ScreenComponent });

/** Champ d'une ligne par langue */
export type TranslationsField = Extract<FormField, { type: 'translations' }>;

export interface FormSection {
  /** Clés de traduction du titre et du sous-titre */
  title: string;
  description?: string;
  /** Type de la section, qui fixe son icône (SECTION_ICONS) */
  kind?: SectionKind;
  fields: FormField[];
}

/** Informations affichées à côté du formulaire */
export type FormMeta = 'createdAt' | 'updatedAt' | 'author' | 'pageCount';

export interface FormScreenDescriptor<T extends ScreenItem = ScreenItem> {
  /** Chemin de la collection (/admin/tags) */
  resource: string;
  /** Clés de traduction, {id} dans celle de l'édition */
  title: { create: string; edit: string };
  /** Clés de traduction du texte sous le titre */
  description?: { create: string; edit: string };
  icon?: IconName;
  /** Route de la liste */
  back: string;
  /** Route d'édition, ouverte après une création */
  editRoute: string;
  leaveKey: string;
  initial: () => Partial<T>;
  sections: FormSection[];
  meta?: FormMeta[];
  /** Opération de lecture : page d'édition introuvable sans elle */
  read: { operation: string };
  /** Opération de création, exigée par une route de création : page introuvable sans elle */
  create?: { operation: string };
  /** Opération de modification, pour savoir si l'élément est modifiable */
  update: { operation: string };
  delete?: { operation: string };
  /** Clés de traduction des notifications et de la suppression, {label} */
  messages: { created: string; saved: string; confirmDelete: string; deleted: string };
  rowLabel: (item: T) => string;
}

export interface FormScreenState<T extends ScreenItem = ScreenItem> {
  descriptor: FormScreenDescriptor<T>;
  resource: AdminResource<T>;
  /** Création pas encore enregistrée */
  isNew: Ref<boolean>;
  title: Ref<string>;
  /** Texte sous le titre, vide sans description */
  description: Ref<string>;
  locales: string[];
  /** Langue de l'interface, recopiée vers les autres quand la recopie est cochée */
  currentLocale: string;
  /** Langue ouverte dans les onglets des champs par langue, la même pour toutes les sections */
  activeLocale: Ref<string>;
  /** Modifications non enregistrées */
  dirty: Ref<boolean>;
  canDelete: Ref<boolean>;
  canEdit: Ref<boolean>;
  /** Valeur d'un champ translations dans une langue */
  translation(field: TranslationsField, locale: string): string;
  setTranslation(field: TranslationsField, locale: string, value: string): void;
  /** Recopie de la langue de l'interface vers les autres langues des champs copyFromCurrent, cochée à la création */
  copying: Ref<boolean>;
  /** Coche ou décoche la recopie ; cochée, elle recopie aussitôt la langue de l'interface */
  setCopying(value: boolean): void;
  /** Champ d'une langue rempli par la recopie, non modifiable */
  isCopied(field: TranslationsField, locale: string): boolean;
  /** Chemin d'erreur du champ d'une langue (translations[1].label) */
  translationPath(field: TranslationsField, locale: string): string;
  save(): Promise<void>;
  remove(): Promise<void>;
  reload(): Promise<void>;
  /**
   * Écriture faite ailleurs : recharge l'élément, signale le conflit s'il est modifié,
   * revient à la liste s'il est supprimé
   */
  externalChange(change: ResourceChange): Promise<void>;
  back(): void;
}
