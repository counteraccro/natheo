/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Ensemble de fonctions utilitaires pour le front
 */
import type { FrontDatas, FrontLinkable, FrontOptionsSystem } from '@/ts/Front/NatheoHorizon/Structure.type';

const THEME_STORAGE_KEY = 'theme';

export class UtilsFront {
  constructor(
    readonly datas: FrontDatas,
    readonly optionsSystem: FrontOptionsSystem
  ) {}

  /**
   * Construit l'url (relative au site) d'un élément de menu ou d'une page
   */
  getUrl(element: FrontLinkable): string {
    if (element.url) {
      return element.url;
    }

    let category = '';
    if (element.category !== undefined && element.category !== '') {
      category = (this.datas.pageCategories[Number(element.category)] ?? '') + '/';
    }

    return `${this.datas.basePath}/${this.datas.locale}/${category}${element.slug ?? ''}`;
  }

  /**
   * Formate un timestamp (en millisecondes) en date longue dans la locale courante
   */
  formatDate(timestamp: number): string {
    return new Intl.DateTimeFormat(this.datas.locale, {
      day: '2-digit',
      month: 'long',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    }).format(new Date(timestamp));
  }

  /**
   * Formate un timestamp (en millisecondes) au format "il y a ..." dans la locale courante
   */
  timeAgo(timestamp: number): string {
    const rtf = new Intl.RelativeTimeFormat(this.datas.locale, { numeric: 'auto' });
    const diffMinutes = Math.round((timestamp - Date.now()) / 60000);

    if (Math.abs(diffMinutes) < 1) {
      return rtf.format(0, 'minute');
    }
    if (Math.abs(diffMinutes) < 60) {
      return rtf.format(diffMinutes, 'minute');
    }
    if (Math.abs(diffMinutes) < 1440) {
      return rtf.format(Math.round(diffMinutes / 60), 'hour');
    }
    return rtf.format(Math.round(diffMinutes / 1440), 'day');
  }

  /**
   * Retourne les initiales (2 lettres max) d'une chaîne
   */
  getInitials(value: string): string {
    const parts = value.trim().split(/\s+/);
    if (parts.length >= 2) {
      return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return parts[0].slice(0, 2).toUpperCase();
  }

  /**
   * Indique si l'utilisateur connecté peut modérer les commentaires
   */
  isUserCanModerate(): boolean {
    return this.datas.userInfo?.canModerate === true;
  }

  /**
   * Bascule le mode sombre et mémorise le choix
   */
  static toggleDarkMode(): void {
    const isDark = document.documentElement.classList.toggle('dark');
    try {
      localStorage.setItem(THEME_STORAGE_KEY, isDark ? 'dark' : 'light');
    } catch {
      // Stockage indisponible (navigation privée) : le choix ne sera simplement pas mémorisé
    }
  }

  /**
   * Numérote les lignes des blocs de code contenus dans un élément
   */
  static addCodeLineNumbers(root: HTMLElement): void {
    root.querySelectorAll<HTMLElement>('pre code').forEach((codeEl) => {
      if (codeEl.dataset.lnProcessed === '1') {
        return;
      }

      const lines = codeEl.innerHTML.split(/\r?\n/);
      if (lines.length && lines[lines.length - 1].trim() === '') {
        lines.pop();
      }

      codeEl.innerHTML = lines
        .map((line) => `<span class="code-line">${line.length ? line : '&nbsp;'}</span>`)
        .join('');
      codeEl.dataset.lnProcessed = '1';
    });
  }
}
