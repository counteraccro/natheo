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
     * @param userToken
     */
    constructor(tabUrls, userToken) {
        super();
        this.tabUrls = tabUrls;
        this.userToken = userToken;
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

    /**
     * Récupère le contenu d'une page
     * @param params
     * @param successCallBack
     * @param failureCallBack
     * @param loaderCallBack
     */
    getContentPage(params, successCallBack, failureCallBack, loaderCallBack) {
        let url = this.addParameters(this.tabUrls.apiPageContent, params)
        this.getRequest(url, successCallBack, failureCallBack, loaderCallBack);
    }

    /**
     * Modère un commentaire
     * @param id
     * @param data
     * @param successCallBack
     * @param failureCallBack
     * @param loaderCallBack
     */
    putModerate(id, data, successCallBack, failureCallBack, loaderCallBack) {
        let url = this.tabUrls.apiModerateComment.replace('/0', '/' + id);
        this.putRequest(url, data, successCallBack, failureCallBack, loaderCallBack)
    }

    addComment(data, successCallBack, failureCallBack, loaderCallBack) {
        let url = this.tabUrls.apiAddComment;
        this.putRequest(url, data, successCallBack, failureCallBack, loaderCallBack)
    }

    /**
     * Retourne les commentaires d'une page
     * @param params
     * @param successCallBack
     * @param failureCallBack
     * @param loaderCallBack
     */
    getCommentByPage(params, successCallBack, failureCallBack, loaderCallBack) {
        let url = this.addParameters(this.tabUrls.apiCommentsByPage, params)
        this.getRequest(url, successCallBack, failureCallBack, loaderCallBack);
    }
}

export {AjaxApiRequest};