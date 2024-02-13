<script>
import axios from "axios";

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet d'afficher la liste des notifications
 */
export default {
  name: "NotificationList",
  props: {
    url: String,
    urlPurge: String,
    page: Number,
    limit: Number,
  },
  data() {
    return {
      notifications: [],
      translation: Object,
      urlRead: '',
      urlReadAll: '',
      listLimit: Object,
      cLimit: this.limit,
      loading: false,
      locale: '',
      onlyNotRead: 1,
      allReadSuccess: false,
      allReadBtn: true,
    }
  },
  mounted() {
    this.loading = true;
    this.purge()
  },
  methods: {
    /**
     * Chargement des données
     * @param page
     * @param limit
     * @param onlyNotRead
     */
    loadData(page, limit, onlyNotRead) {

      this.loading = true;
      axios.get(this.url + '/' + page + '/' + limit + '/' + onlyNotRead).then((response) => {
        this.notifications = response.data.notifications;
        this.translation = response.data.translation;
        this.urlRead = response.data.urlRead;
        this.urlReadAll = response.data.urlReadAll;
        this.locale = response.data.locale;
        this.listLimit = JSON.parse(response.data.listLimit);
      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.loading = false
        this.canAllRead();
      });
    },

    /**
     * Active ou désactive le bouton non-lu
     */
    canAllRead() {
      let nbElement = document.getElementById('badge-notification');
      if (nbElement === null) {
        this.allReadBtn = false;
      } else {
        this.allReadBtn = true;
      }
    },

    /**
     * Charge uniquement les notifications non lu
     */
    loadOnlyNotRead() {
      this.onlyNotRead = 0;
      this.loadData(1, 500, 1);
    },

    /**
     * Charge toutes les notifications
     */
    loadAll() {
      this.onlyNotRead = 1;
      this.loadData(1, this.limit, 0);
    },

    /**
     * Met toutes les notifications non lu en lu
     */
    readAll() {

      let nbElement = document.getElementById('badge-notification');
      nbElement.remove();

      this.loading = true;
      axios.post(this.urlReadAll, {}).then((response) => {
      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.allReadSuccess = true;
        setTimeout(() => {
          this.allReadSuccess = false;
        }, 5000)
        this.loadData(this.page, this.limit, 0)
      });
    },

    /** Lance la purge des notifications **/
    purge() {

      axios.post(this.urlPurge, {}).then((response) => {
      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.loadData(this.page, this.limit, 0)
      });
    },

    /**
     * Au survol de la sourie
     * @param id
     */
    mouseover(id) {
      let element = document.getElementById('notification-' + id);
      let nbElement = document.getElementById('badge-notification');
      if (element.classList.contains("no-read")) {
        element.classList.remove("no-read");

        axios.post(this.urlRead, {
          'id': id,
        }).then((response) => {
        }).catch((error) => {
          console.error(error);
        }).finally();

        nbElement.innerHTML = parseInt(nbElement.innerHTML) - 1;
        if (nbElement.innerHTML === '0') {
          nbElement.remove();
        }
      }
    },

    changeLimit(limit) {
      this.cLimit = limit;
      this.loadData(1, limit, 0)
    },

    /**
     * Mise en page class
     * @param notification
     * @returns {string}
     */
    cssClass(notification) {

      let returnClass = '';
      if (notification.read === false) {
        returnClass += ' no-read';
      }

      if (notification.level === 2) {
        returnClass += ' bg-warning';
      } else if (notification.level === 3) {
        returnClass += ' bg-danger';
      } else {
        returnClass += ' bg-light';
      }

      return returnClass;
    },

    /**
     * Affiche le bon format de la date
     * @param dateString
     * @returns {string}
     */
    formatDate(dateString) {
      const date = new Date(dateString);
      return new Intl.DateTimeFormat(this.locale, {dateStyle: 'long', timeStyle: 'short'}).format(date);
    }
  }
}
</script>

<template>

  <div :class="this.loading === true ? 'block-grid' : ''">
    <div v-if="this.loading" class="overlay">
      <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1000;">
        <div class="spinner-border text-primary" role="status"></div>
        <span class="txt-overlay">{{ this.translation.loading }}</span>
      </div>
    </div>

    <div v-if="this.notifications.length > 0">
      <div class="btn-group">
        <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
          {{ this.translation.nb_notifification_show_start }} {{ this.cLimit }} {{ this.translation.nb_notifification_show_end }}
        </button>
        <ul class="dropdown-menu">
          <li v-for="(i) in this.listLimit">
            <a class="dropdown-item" href="#" :data-limit="i" @click="this.changeLimit(i)">{{ i }}</a></li>
        </ul>
      </div>
      <div v-if="onlyNotRead === 1" class="btn btn-secondary ms-2" @click="this.loadOnlyNotRead()">{{ this.translation.onlyNotRead }}</div>
      <div v-else class="btn btn-secondary ms-2" @click="this.loadAll()">{{ this.translation.all }}</div>
      <div class="btn btn-secondary ms-2" @click="this.readAll()" :class="allReadBtn ? '' : 'disabled'">{{ this.translation.readAll }}</div>

      <div class="clearfix"></div>

      <div v-if="allReadSuccess" class="alert alert-success mt-4">{{ this.translation.allSuccess }}</div>

      <div class="mt-4">
        <div v-for="notification in this.notifications">
          <div :id="'notification-' + notification.id" class="card bg-opacity-10 p-2 shadow-sm rounded-end mb-2" :class="this.cssClass(notification)" @mouseover="mouseover(notification.id)">
            <div class="card-body">
              <div class="float-end"><i>{{ this.formatDate(notification.createdAt) }}</i></div>
              <h5 class="card-title">{{ notification.title }}</h5>
              <span class="card-text" v-html="notification.content"></span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-else class="mt-4">
      <button v-if="!loading" class="btn btn-secondary disabled">
        {{ this.translation.nb_notifification_show_start }} {{ this.cLimit }} {{ this.translation.nb_notifification_show_end }}
      </button>
      <div v-if="!loading" class="btn btn-secondary ms-2" @click="this.loadAll()">{{ this.translation.all }}</div>
      <div v-if="!loading" class="btn btn-secondary ms-2 disabled">{{ this.translation.readAll }}</div>
      <div class="card mt-4">
        <div class="card-body">
          <div class="text-center">
            <i>{{ this.translation.empty }}</i>
          </div>
        </div>
      </div>
    </div>
  </div>

</template>

<style scoped>

</style>