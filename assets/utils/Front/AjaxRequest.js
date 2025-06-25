/**
 * Regroupe l'ensemble des appels API
 * @author Gourdon Aymeric
 * @version 1.0
 */

import axios from "axios";

class AjaxRequest {

    tabUrls;
    token = 'read.CZjfAZu6FatHfrCU8MaCudqc.GfmytciCqV8P236QSu3jJizG.EfgV96RRTSTxeqVBDHTxX2yh.9xicEZXkXzx7hL85eUZ8YrEJ';

    constructor(tabUrls) {
        this.tabUrls = tabUrls;

    }

    get getTabUrls() {
        return this.tabUrls;
    }

    getPageBySlug()
    {
        console.log('ici');
        axios.get(this.tabUrls.apiPageFind, {}).then((response) => {

        }).catch((error) => {
            console.log(error);
        }).finally(() => {

        });
    }

}

export {AjaxRequest};