export interface FaqTranslation {
  id: number;
  faq: number;
  locale: string;
  title: string;
}

export interface FaqCategoryTranslation {
  id: number;
  faqCategory: number;
  locale: string;
  title: string;
}

export interface FaqQuestionTranslation {
  id: number;
  FaqQuestion: number;
  locale: string;
  title: string;
  answer: string;
  [key: string]: string | number; // pour l'accès dynamique via tmp[2]
}

export interface FaqQuestion {
  id: number;
  faqCategory: number;
  disabled: boolean;
  renderOrder: number;
  faqQuestionTranslations: FaqQuestionTranslation[];
}

export interface FaqCategory {
  id: number;
  faq: number;
  disabled: boolean;
  renderOrder: number;
  faqCategoryTranslations: FaqCategoryTranslation[];
  faqQuestions: FaqQuestion[];
}

export interface FaqStatistique {
  id: number;
  faq: number;
  key: string;
  value: string;
}

export interface Faq {
  id: number;
  disabled: boolean;
  faqTranslations: FaqTranslation[];
  faqCategories: FaqCategory[];
  faqStatistiques: FaqStatistique[];
}
