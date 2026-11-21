/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Description d'un écran de formulaire, état calculé par useFormScreen() et affiché par le thème
 */
import type { Ref } from 'vue';
import type { AdminResource } from '@/vue/Composables/Admin/useAdminResource';
import type { IconName } from '@/ts/Global/Ui/UiIcon.type';
import type { ScreenComponent, ScreenItem } from '@/ts/Admin/Screen/Screen.type';

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
  title: string;
  fields: FormField[];
}

/** Informations affichées à côté du formulaire */
export type FormMeta = 'createdAt' | 'updatedAt' | 'author' | 'pageCount';

export interface FormScreenDescriptor<T extends ScreenItem = ScreenItem> {
  /** Chemin de la collection (/admin/tags) */
  resource: string;
  /** Clés de traduction, {id} dans celle de l'édition */
  title: { create: string; edit: string };
  icon?: IconName;
  /** Route de la liste */
  back: string;
  /** Route d'édition, ouverte après une création */
  editRoute: string;
  leaveKey: string;
  initial: () => Partial<T>;
  sections: FormSection[];
  meta?: FormMeta[];
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
  locales: string[];
  /** Langue de l'interface, recopiée vers les autres à la création */
  currentLocale: string;
  canDelete: Ref<boolean>;
  canEdit: Ref<boolean>;
  /** Valeur d'un champ translations dans une langue */
  translation(field: TranslationsField, locale: string): string;
  setTranslation(field: TranslationsField, locale: string, value: string): void;
  /** Chemin d'erreur du champ d'une langue (translations[1].label) */
  translationPath(field: TranslationsField, locale: string): string;
  save(): Promise<void>;
  remove(): Promise<void>;
  reload(): Promise<void>;
  back(): void;
}
