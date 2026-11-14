/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Réseaux sociaux renseignés dans les options du site, dans un ordre fixe
 */
import { computed, type ComputedRef } from 'vue';
import { useSiteOptions } from '@/vue/Composables/Front/useSiteOptions';
import type { SiteOptions } from '@/ts/Front/Api/OptionSystem.type';

export type SocialNetwork = 'github' | 'linkedin' | 'youtube' | 'x' | 'facebook' | 'instagram' | 'tiktok';

export interface SocialLink {
  network: SocialNetwork;
  label: string;
  url: string;
}

const NETWORKS: { network: SocialNetwork; label: string; key: keyof SiteOptions }[] = [
  { network: 'github', label: 'GitHub', key: 'front_footer_social_github_url' },
  { network: 'linkedin', label: 'LinkedIn', key: 'front_footer_social_linkedin_url' },
  { network: 'youtube', label: 'YouTube', key: 'front_footer_social_youtube_url' },
  { network: 'x', label: 'X', key: 'front_footer_social_x_url' },
  { network: 'facebook', label: 'Facebook', key: 'front_footer_social_facebook_url' },
  { network: 'instagram', label: 'Instagram', key: 'front_footer_social_instagram_url' },
  { network: 'tiktok', label: 'TikTok', key: 'front_footer_social_tiktok_url' },
];

export function useSocialLinks(): ComputedRef<SocialLink[]> {
  const { data: options } = useSiteOptions();
  return computed(() =>
    NETWORKS.flatMap(({ network, label, key }) => {
      const url = options.value?.[key];
      return typeof url === 'string' && url !== '' ? [{ network, label, url }] : [];
    })
  );
}
