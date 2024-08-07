/**
 * Ensemble de fonction qui permet me manipuler les menuElements
 * @author Gourdon Aymeric
 * @version 1.0
 */

class MenuElementTools {

    /**
     * Retourne un élement depuis la liste des élements en fonction de son id
     * @param elements
     * @param id
     * @returns {null}
     */
    static getElementMenuById(elements, id) {
        let element = null;

        Object.entries(elements).forEach((value) => {
            let obj = value[1];
            if (obj.id === id) {
                element = obj;
            } else if (obj.hasOwnProperty('children') && obj.children.length > 0 && element === null) {
                element = this.getElementMenuById(obj.children, id)
            }
        });
        return element;
    }

    /**
     * Calcul la valeur de columnMax et rowMax en fonction du parent
     * @param elements
     * @param idParent
     */
    static calculMaxColAndRowMaxByIdParent(elements, idParent) {

        /**
         * Fonction temporaire pour calculer columnMax et rowMax en fonction
         * de la column
         * @param data
         * @param obj
         * @returns {*}
         */
        function tmp(data, obj) {
            if (obj.columnPosition > data.columnMax) {
                data.columnMax = obj.columnPosition;
                data[obj.columnPosition] = {'colum': obj.columnPosition, 'rowMax': 0};
            }

            if (obj.rowPosition > data[obj.columnPosition].rowMax) {
                data[obj.columnPosition].rowMax = obj.rowPosition;
            }
            return data;
        }

        let data = {'columnMax': 0}
        Object.entries(elements).forEach((value) => {
            let obj = value[1];
            if (obj.hasOwnProperty('parent') && idParent === obj.parent) {
                data = tmp(data, obj);
            } else if (obj.hasOwnProperty('children') && obj.children.length && idParent !== null && data.columnMax === 0) {
                data = this.calculMaxColAndRowMaxByIdParent(obj.children, idParent);
            } else if (idParent === null || idParent === 0) {
                data = tmp(data, obj);
            }
        });
        return data;
    }

    /**
     * Retourne le lien avec la traduction en fonction de la locale
     * @param tabMenuElementTranslation
     * @param locale
     */
    static getTranslationByLocale(tabMenuElementTranslation, locale) {

        let element = ['text', 'link'];

        tabMenuElementTranslation.forEach((menuElementTranslation) => {
            if (menuElementTranslation.locale === locale) {
                element['text'] = menuElementTranslation.textLink;
                if (menuElementTranslation.hasOwnProperty('link') && menuElementTranslation.link !== "") {
                    element['link'] = menuElementTranslation.link;
                } else {
                    element['link'] = menuElementTranslation.externalLink;
                }

            }
        })
        return element;
    }
}

export {MenuElementTools};