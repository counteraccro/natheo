/**
 * Types pour le composant TagForm
 * @author Gourdon Aymeric
 * @version 1.0
 */

export interface TagFormTranslate {
  loading: string;
  formTitleCreate: string;
  formTitleUpdate: string;
  formInputColorLabel: string;
  formInputColorError: string;
  formInputColorHelp: string;
  formInputLabelLabel: string;
  formInputLabelPlaceholder: string;
  formInputLabelError: string;
  linkColorChoice: string;
  colorTitle: string;
  colorDescription: string;
  labelCurrent: string;
  labelOther: string;
  autoCopy: string;
  renduTitle: string;
  btnSubmitUpdate: string;
  btnSubmitCreate: string;
  statTitle: string;
  formDisabledLabel: string;
  btnCancel: string;
  btnDelete: string;
  successSave: string;
  errorSave: string;
  modaleConfirmDeleteTitle: string;
  modaleConfirmDeleteMessage: string;
  modaleConfirmDeleteBtnOK: string;
  modaleConfirmDeleteBtnKo: string;
  styleTitle: string;
  styleDescription: string;
  textTitle: string;
  textDescription: string;
}

export interface TagTranslation {
  id: number | null;
  locale: string;
  label: string | null;
}

export interface Tag {
  id: number | null;
  color: string;
  tagTranslations: TagTranslation[];
}

export interface TagFormLocales {
  locales: string[];
  localesTranslate: Record<string, string>;
  current: string;
}
