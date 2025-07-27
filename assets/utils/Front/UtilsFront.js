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

        if(element.url !== "") {
            return element.url;
        }

        let category = '';
        if(element.category !== '') {
            category = this.getStringPageCategoryById(element.category).toLowerCase() + '/';
        }

        if(this.optionsSystem.OS_ADRESSE_SITE.search(/http/gm) !== -1) {
            return this.optionsSystem.OS_ADRESSE_SITE + '/' + this.data.locale + '/' + category + element.slug
        }

        return 'https://' + this.optionsSystem.OS_ADRESSE_SITE + '/' + this.data.locale + '/' + category + element.slug
    }
}

export {UtilsFront}