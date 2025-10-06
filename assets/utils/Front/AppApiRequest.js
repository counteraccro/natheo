/**
 * App des request pour l'API
 * @author Gourdon Aymeric
 * @version 1.0
 */
import axios from 'axios';
import { h } from 'vue';

class AppApiRequest {
  token =
    'Bearer read.CZjfAZu6FatHfrCU8MaCudqc.GfmytciCqV8P236QSu3jJizG.EfgV96RRTSTxeqVBDHTxX2yh.9xicEZXkXzx7hL85eUZ8YrEJ';

  /**
   * Token authentication
   * @type {string}
   */
  userToken = '';

  /**
   * Header Authentification
   * @return {{Authorization: string}}
   */
  getHeader() {
    let header = {
      Authorization: this.token,
    };

    if (this.userToken !== '') {
      header['User-Token'] = this.userToken;
    }

    return header;
  }

  /**
   * Requête get
   * @param url
   * @param successCallBack
   * @param failureCallBack
   * @param loaderCallBack
   */
  getRequest(url, successCallBack, failureCallBack, loaderCallBack = null) {
    axios
      .get(url, {
        headers: this.getHeader(),
      })
      .then((response) => {
        successCallBack(response.data.data);
      })
      .catch((error) => {
        failureCallBack(error.response.data.code_http, error.response.data.errors[0]);
      })
      .finally(() => {
        if (loaderCallBack !== null) {
          loaderCallBack(true);
        }
      });
  }

  /**
   * Requete put
   * @param url
   * @param data
   * @param successCallBack
   * @param failureCallBack
   * @param loaderCallBack
   */
  putRequest(url, data, successCallBack, failureCallBack, loaderCallBack = null) {
    axios
      .put(url, data, {
        headers: this.getHeader(),
      })
      .then((response) => {
        successCallBack(response.data.data);
      })
      .catch((error) => {
        failureCallBack(error.response.data.code_http, error.response.data.errors[0]);
      })
      .finally(() => {
        if (loaderCallBack !== null) {
          loaderCallBack(true);
        }
      });
  }

  /**
   * Requete post
   * @param url
   * @param data
   * @param successCallBack
   * @param failureCallBack
   * @param loaderCallBack
   */
  postRequest(url, data, successCallBack, failureCallBack, loaderCallBack = null) {
    axios
      .post(url, data, {
        headers: this.getHeader(),
      })
      .then((response) => {
        successCallBack(response.data.data);
      })
      .catch((error) => {
        failureCallBack(error.response.data.code_http, error.response.data.errors[0]);
      })
      .finally(() => {
        if (loaderCallBack !== null) {
          loaderCallBack(true);
        }
      });
  }

  /**
   * Ajoute les paramètres à l'url en paramètre
   * @param url
   * @param params
   * @returns {*}
   */
  addParameters(url, params) {
    let i = 0;
    Object.keys(params).forEach((key) => {
      let caract = '&';
      if (i === 0) {
        caract = '?';
      }
      if (params[key] !== null && params[key] !== '') {
        url += caract + key + '=' + params[key];
        i++;
      }
    });
    return url;
  }
}

export { AppApiRequest };
