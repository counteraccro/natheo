/**
 * Regroupe l'ensemble des appels API Ajax
 * @author Gourdon Aymeric
 * @version 1.0
 */

import {AppApiRequest} from './AppApiRequest'

class AjaxApiRequest extends AppApiRequest {

    tabUrls;

    /**
     * @param tabUrls
     */
    constructor(tabUrls) {
        super();
        this.tabUrls = tabUrls;
    }

    /**
     * Retourne une page en fonction du slug
     * @param params
     * @param successCallBack
     * @param failureCallBack
     * @param loaderCallBack
     */
    getPageBySlug(params, successCallBack, failureCallBack, loaderCallBack) {
        let url = this.addParameters(this.tabUrls.apiPageFind, params);
        this.getRequest(url, successCallBack, failureCallBack, loaderCallBack);
    }

    /**
     * Retourne la liste des options systèmes API
     * @param successCallBack
     * @param failureCallBack
     * @param loaderCallBack
     */
    getOptionSystems(successCallBack, failureCallBack, loaderCallBack) {
        this.getRequest(this.tabUrls.apiOptionsSystems, successCallBack, failureCallBack, loaderCallBack);
    }
}

export {AjaxApiRequest};