export type ToastType = 'success' | 'danger';

export type ToastItem = {
  id: number;
  type: ToastType;
  msg: string;
};
