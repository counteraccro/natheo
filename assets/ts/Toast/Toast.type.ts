export type Toast = {
  show: boolean;
  msg: string;
};

export type Toasts = {
  [key: string]: Toast;
};
