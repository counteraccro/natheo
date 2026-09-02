import type {
  InstallationStepOneTranslate,
  BddConfig,
  UpdateEnvPayload,
  UpdateEnvResponse,
} from '@/ts/Installation/InstallationStepOne.type';

export type InstallationStepTwoTranslate = InstallationStepOneTranslate;

export interface InstallationStepTwoUrls {
  check_action_bdd: string;
  update_env: string;
  create_bdd: string;
  create_schema: string;
  update_app_secret: string;
  step_3: string;
  step_1: string;
}

export interface InstallationStepTwoLocales {
  [key: string]: string;
}

export interface InstallationStepTwoOptionCheck {
  database_exist: string;
}

export interface InstallationStepTwoConfigKey {
  database_url: string;
}

export interface InstallationStepTwoBddParams {
  database_prefix: string;
}

export interface InstallationStepTwoDatas {
  bdd_config: BddConfig;
  config_key: InstallationStepTwoConfigKey;
  option_check: InstallationStepTwoOptionCheck;
  bdd_params: InstallationStepTwoBddParams;
}

export type ValidableField = 'bdd_name' | 'version' | 'charset';

export type DataBaseFieldErrors = Record<ValidableField, string>;

export interface CheckActionResult {
  success: boolean;
  message: string;
}

export interface CheckActionBddResponse {
  database_exist: CheckActionResult;
}

export interface ApiActionResponse {
  success: boolean;
  error?: string;
}

export type CreateStepKey = 'updateEnv' | 'updateSecret' | 'createBdd' | 'createTable';

export interface CreateStepResult {
  success: boolean;
  message: string;
}

export type CreateStepStatus = Record<CreateStepKey, CreateStepResult>;

export type { UpdateEnvPayload, UpdateEnvResponse };
