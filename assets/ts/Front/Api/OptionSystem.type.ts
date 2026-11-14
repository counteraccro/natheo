/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource OptionSystem de l'API v2 : options publiques du site
 */
import type { JsonLdResource } from '@/ts/Front/Api/Hydra.type';

export interface OptionSystem extends JsonLdResource {
  /** Clé en minuscules, sans le préfixe OS_ */
  key: string;
  value: boolean | string | null;
}

/** Options publiques par clé, booléens déjà typés par l'API */
export interface SiteOptions {
  site_name: string | null;
  adresse_site: string | null;
  theme_front_site: string | null;
  open_comment: boolean;
  front_footer_texte: string | null;
  front_script_top: string | null;
  front_script_start_body: string | null;
  front_script_end_body: string | null;
  front_footer_social_facebook_url: string | null;
  front_footer_social_instagram_url: string | null;
  front_footer_social_github_url: string | null;
  front_footer_social_tiktok_url: string | null;
  front_footer_social_linkedin_url: string | null;
  front_footer_social_youtube_url: string | null;
  front_footer_social_x_url: string | null;
  front_robot_no_index: boolean;
  front_robot_no_follow: boolean;
}
