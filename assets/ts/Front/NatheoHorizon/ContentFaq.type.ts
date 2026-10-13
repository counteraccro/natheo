/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Types pour le composant ContentFaq
 */

export interface FaqQuestion {
  title: string;
  answer: string;
}

export interface FaqCategory {
  title: string;
  questions: FaqQuestion[];
}

export interface ContentFaqResponse {
  id: number;
  content: {
    title: string;
    categories: FaqCategory[];
  };
}
