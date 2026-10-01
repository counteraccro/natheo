export type TranslateUpdate = {
  key: string;
  value: string;
};

export type TranslateLanguagesResponse = {
  languages: Record<string, string>;
};

export type TranslateFilesResponse = {
  files: Record<string, string>;
};

export type TranslateFileResponse = {
  success: true;
  file: Record<string, string>;
};

export type TranslateErrorResponse = {
  success: false;
  msg: string;
};

export type TranslateSavePayload = {
  file: string;
  translates: TranslateUpdate[];
};

export type TranslateSaveResponse = {
  success: boolean;
  msg: string;
  errors: Record<string, string>;
};
