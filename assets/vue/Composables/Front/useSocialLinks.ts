/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Réseaux sociaux renseignés dans les options du site, dans un ordre fixe
 */
import { computed, type ComputedRef } from 'vue';
import { useSiteOptions } from '@/vue/Composables/Front/useSiteOptions';
import type { SiteOptions } from '@/ts/Front/Api/OptionSystem.type';
import type { IconName } from '@/ts/Global/Ui/UiIcon.type';

export type SocialNetwork = 'github' | 'linkedin' | 'youtube' | 'x' | 'facebook' | 'instagram' | 'tiktok';

export interface SocialLink {
  network: SocialNetwork;
  label: string;
  url: string;
  /** Icône du registre de UiIcon */
  icon: IconName;
}

// TikTok n'a pas d'icône dans Flowbite : globe générique
const NETWORKS: { network: SocialNetwork; label: string; key: keyof SiteOptions; icon: IconName }[] = [
  { network: 'github', label: 'GitHub', key: 'front_footer_social_github_url', icon: 'github' },
  { network: 'linkedin', label: 'LinkedIn', key: 'front_footer_social_linkedin_url', icon: 'linkedin' },
  { network: 'youtube', label: 'YouTube', key: 'front_footer_social_youtube_url', icon: 'youtube' },
  { network: 'x', label: 'X', key: 'front_footer_social_x_url', icon: 'x' },
  { network: 'facebook', label: 'Facebook', key: 'front_footer_social_facebook_url', icon: 'facebook' },
  { network: 'instagram', label: 'Instagram', key: 'front_footer_social_instagram_url', icon: 'instagram' },
  { network: 'tiktok', label: 'TikTok', key: 'front_footer_social_tiktok_url', icon: 'globe' },
];

export function useSocialLinks(): ComputedRef<SocialLink[]> {
  const { data: options } = useSiteOptions();
  return computed(() =>
    NETWORKS.flatMap(({ network, label, key, icon }) => {
      const url = options.value?.[key];
      return typeof url === 'string' && url !== '' ? [{ network, label, url, icon }] : [];
    })
  );
}
