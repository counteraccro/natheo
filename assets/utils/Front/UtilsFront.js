/**
 * Ensemble de fonction utils pour le front
 * @author Gourdon Aymeric
 * @version 1.0
 */

class UtilsFront {
  data;
  optionsSystem;

  constructor(data, optionSystem) {
    this.data = data;
    this.optionsSystem = optionSystem;
  }

  /**
   * Retourne une catégorie en fonction de son id
   * @param id
   */
  getStringPageCategoryById(id) {
    return this.data.pageCategories[id];
  }

  /**
   * Construit l'url en fonction de l'élément
   * @param element
   * @returns {*|string}
   */
  getUrl(element) {
    if (Object.hasOwn(element, 'url') && element.url !== '') {
      return element.url;
    }

    let category = '';
    if (Object.hasOwn(element, 'category') && element.category !== '') {
      category = this.getStringPageCategoryById(element.category).toLowerCase() + '/';
    }

    if (this.optionsSystem.OS_ADRESSE_SITE.search(/http/gm) !== -1) {
      return this.optionsSystem.OS_ADRESSE_SITE + '/' + this.data.locale + '/' + category + element.slug;
    }

    return 'https://' + this.optionsSystem.OS_ADRESSE_SITE + '/' + this.data.locale + '/' + category + element.slug;
  }

  /**
   * Format un timestamp en date
   * @param timestamp
   * @returns {string}
   */
  formatDate(timestamp) {
    let locale = this.data.locale + '-' + this.data.locale.toUpperCase();

    let date = new Date(timestamp);
    return new Intl.DateTimeFormat(locale, {
      day: '2-digit',
      month: 'long',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
    }).format(date);
  }

  /**
   * Evènement pour afficher le menu mobile
   */
  eventDropDownMobileToggle() {
    document.getElementById('mobile-menu-button').addEventListener('click', function () {
      const mobileMenu = document.getElementById('mobile-menu');
      const isExpanded = this.getAttribute('aria-expanded') === 'true';
      this.setAttribute('aria-expanded', !isExpanded);
      mobileMenu.classList.toggle('hidden');
    });

    // Mobile dropdown toggles
    document.querySelectorAll('.mobile-dropdown-trigger').forEach((trigger) => {
      trigger.addEventListener('click', function () {
        const content = this.nextElementSibling;
        content.classList.toggle('hidden');
      });
    });
  }

  /**
   * Enregistre le mode sombre ou claire
   * @param mode
   */
  switchDarkMode(mode) {
    document.documentElement.classList.toggle('dark');
    localStorage.setItem('theme', mode);
  }

  /**
   * Calcul une date au format il y'a
   * @param timestamp
   * @returns {string}
   */
  timeAgo(timestamp) {
    let now = Date.now();
    let diffMs = now - timestamp; // différence en millisecondes
    let diffMinutes = Math.floor(diffMs / 60000); // conversion en minutes

    if (diffMinutes < 1) {
      return 'il y a quelques secondes';
    } else if (diffMinutes < 60) {
      return `il y a ${diffMinutes} min`;
    } else if (diffMinutes < 1440) {
      let hours = Math.floor(diffMinutes / 60);
      return `il y a ${hours} h`;
    } else {
      let days = Math.floor(diffMinutes / 1440);
      return `il y a ${days} j`;
    }
  }

  /**
   * Récupère les premières lettres d'une string
   * @param string
   * @returns {string}
   */
  getInitials(string) {
    let parts = string.trim().split(/\s+/); // coupe sur espaces multiples
    if (parts.length >= 2) {
      return (parts[0][0] + parts[1][0]).toUpperCase();
    } else {
      return parts[0].slice(0, 2).toUpperCase();
    }
  }

  /**
   * Vérifie si l'utilisateur peut modérer ou non
   */
  isUserCanModerate() {
    if (this.data.userIno !== '') {
      if (this.data.userInfo.canModerate === true) {
        return true;
      }
    }
    return false;
  }

  /**
   * Retourne l'IP
   */
  getIp() {
    return this.data.ip;
  }
}

export { UtilsFront };
