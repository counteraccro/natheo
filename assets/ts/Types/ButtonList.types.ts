export interface ButtonItem {
  id: string | number;
  label: string;
  css: string;
  disabled?: boolean;
  onClick: (...args: any[]) => void;
  params?: unknown[];
}
