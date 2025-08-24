/**
 * Ensemble de fonction utils pour le front
 * @author Gourdon Aymeric
 * @version 1.0
 */

class UtilsFront {

    data;
    optionsSystem;

    constructor(data, optionSystem) {
        this.data = data
        this.optionsSystem = optionSystem
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

        if(Object.hasOwn(element, 'url') && element.url !== "") {
            return element.url;
        }

        let category = '';
        if(Object.hasOwn(element, 'category') && element.category !== '') {
            category = this.getStringPageCategoryById(element.category).toLowerCase() + '/';
        }

        if(this.optionsSystem.OS_ADRESSE_SITE.search(/http/gm) !== -1) {
            return this.optionsSystem.OS_ADRESSE_SITE + '/' + this.data.locale + '/' + category + element.slug
        }

        return 'https://' + this.optionsSystem.OS_ADRESSE_SITE + '/' + this.data.locale + '/' + category + element.slug
    }

    /**
     * Format un timestamp en date
     * @param timestamp
     * @returns {string}
     */
    formatDate(timestamp) {

        let locale = this.data.locale + '-' + this.data.locale.toUpperCase()

        let date = new Date(timestamp);
        return new Intl.DateTimeFormat(locale, {
            day: "2-digit",
            month: "long",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
            second: "2-digit"
        }).format(date);
    }

    /**
     * Evènement pour afficher le menu mobile
     */
    eventDropDownMobileToggle() {
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !isExpanded);
            mobileMenu.classList.toggle('hidden');
        });

        // Mobile dropdown toggles
        document.querySelectorAll('.mobile-dropdown-trigger').forEach(trigger => {
            trigger.addEventListener('click', function() {
                const content = this.nextElementSibling;
                content.classList.toggle('hidden');
            });
        });
    }
}

export {UtilsFront}