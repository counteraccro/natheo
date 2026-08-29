export interface DashboardStatisticsTranslate {
  total_article: string;
  comments: string;
  comments_waiting: string;
  users: string;
  views: string;
  link_pages: string;
  link_users: string;
  info_cache: string;
}

export interface Urls {
  stats: string;
  user: string;
  comment: string;
  page: string;
}

export interface DashboardStatisticsResponse {
  nbPage: number;
  nbComments: number;
  nbWaitComments: number;
  nbUsers: number;
  nbViews: number;
}
