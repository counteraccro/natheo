export interface OptionUpdatePayload {
  key: string;
  value: string | number;
}

export interface OptionUpdateResponse {
  success: boolean;
  msg?: string;
}

export type OptionField = HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement;
