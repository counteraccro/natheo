export interface InstallationStepFourUrls {
  auth: string;
}

export interface InstallationStepFourTranslate {
  title_h1: string;
  description: string;
  summary_database: string;
  summary_user: string;
  summary_data: string;
  summary_cache: string;
  btn_login: string;
}

export interface InstallationStepFourLocales {
  [key: string]: string;
}

export interface InstallationStepFourDatas {
  [key: string]: unknown;
}
