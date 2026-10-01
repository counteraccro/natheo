/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Types pour le composant Dashboard
 */

import type { BlockHelpFirstConnexionUrls, BlockHelpFirstConnexionTranslate, BlockHelpFirstConnexionDatas } from '@/ts/Dashboard/BlockHelpFirstConnexion.type';
import type { BlockLastCommentUrls, BlockLastCommentTranslate } from '@/ts/Dashboard/BlockLastComment.type';
import type { BlockLastPageUrls, BlockLastPageTranslate } from '@/ts/Dashboard/BlockLastPage.type';
import type { BlockPageMostViewedUrls, BlockPageMostViewedTranslate } from '@/ts/Dashboard/BlockPageMostViewed.type';

export interface DashboardUrls {
  dashboard_help_first_connexion: BlockHelpFirstConnexionUrls;
  dashboard_last_comments: BlockLastCommentUrls;
  dashboard_last_pages: BlockLastPageUrls;
  dashboard_page_most_viewed: BlockPageMostViewedUrls;
}

export interface DashboardTranslate {
  dashboard_help_first_connexion: BlockHelpFirstConnexionTranslate;
  dashboard_last_comments: BlockLastCommentTranslate;
  dashboard_last_pages: BlockLastPageTranslate;
  dashboard_page_most_viewed: BlockPageMostViewedTranslate;
}

export interface DashboardDatas {
  dashboard_help_first_connexion: BlockHelpFirstConnexionDatas;
}

export interface DashboardRoles {
  isUser: boolean;
  isContributeur: boolean;
  isAdmin: boolean;
  isSuperAdmin: boolean;
}
