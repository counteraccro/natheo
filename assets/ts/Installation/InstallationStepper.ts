export interface InstallationStep {
  id: string;
  label: string;
}

export type StepStatus = 'done' | 'active' | 'upcoming';
