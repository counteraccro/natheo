/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Types pour le composant ContentListing
 */

export interface ListingPage {
  img: string | null;
  title: string;
  slug: string;
  category: number;
  author: string;
  created: number;
  update: number;
}

export interface ContentListingResponse {
  id: number;
  title: string;
  content: {
    pages: ListingPage[];
    limit: number;
    current_page: number;
    rows: number;
  };
}
