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
     * @param successCallBack
     * @param failureCallBack
     * @param loaderCallBack
     */
    getPageBySlug(successCallBack, failureCallBack, loaderCallBack) {
        this.getRequest(this.tabUrls.apiPageFind, successCallBack, failureCallBack, loaderCallBack);
    }
}

export {AjaxApiRequest};