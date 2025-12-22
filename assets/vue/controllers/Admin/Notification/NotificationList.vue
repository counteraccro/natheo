<script>
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Permet d'afficher la liste des notifications
 */
import axios from 'axios';
import SkeletonCardStat from '@/vue/Components/Skeleton/CardStat.vue';
import SkeletonTabs from '@/vue/Components/Skeleton/Tabs.vue';

export default {
  name: 'NotificationList',
  components: { SkeletonTabs, SkeletonCardStat },
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
      loadingStat: true,
      locale: '',
      onlyNotRead: 1,
      allReadSuccess: false,
      allReadBtn: true,
    };
  },
  mounted() {
    this.loading = true;
    this.purge();
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
      axios
        .get(this.url + '/' + page + '/' + limit + '/' + onlyNotRead)
        .then((response) => {
          this.notifications = response.data.notifications;
          this.translation = response.data.translation;
          this.urlRead = response.data.urlRead;
          this.urlReadAll = response.data.urlReadAll;
          this.locale = response.data.locale;
          this.listLimit = response.data.listLimit;
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;
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
      axios
        .post(this.urlReadAll, {})
        .then((response) => {})
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.allReadSuccess = true;
          setTimeout(() => {
            this.allReadSuccess = false;
          }, 5000);
          this.loadData(this.page, this.limit, 0);
        });
    },

    /** Lance la purge des notifications **/
    purge() {
      axios
        .post(this.urlPurge, {})
        .then((response) => {})
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loadData(this.page, this.limit, 0);
        });
    },

    /**
     * Au survol de la sourie
     * @param id
     */
    mouseover(id) {
      let element = document.getElementById('notification-' + id);
      let nbElement = document.getElementById('badge-notification');
      if (element.classList.contains('no-read')) {
        element.classList.remove('no-read');

        axios
          .post(this.urlRead, {
            id: id,
          })
          .then((response) => {})
          .catch((error) => {
            console.error(error);
          })
          .finally();

        nbElement.innerHTML = parseInt(nbElement.innerHTML) - 1;
        if (nbElement.innerHTML === '0') {
          nbElement.remove();
        }
      }
    },

    changeLimit(limit) {
      this.cLimit = limit;
      this.loadData(1, limit, 0);
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
      return new Intl.DateTimeFormat(this.locale, { dateStyle: 'long', timeStyle: 'short' }).format(date);
    },
  },
};
</script>

<template>
  <div v-if="this.loadingStat">
    <SkeletonCardStat />
  </div>
  <div v-else class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="card rounded-lg p-4">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium" style="color: var(--text-secondary)">Non lues -tr</p>
          <p class="text-2xl font-bold mt-1" id="unreadCount">5</p>
        </div>
        <div
          class="w-12 h-12 rounded-lg flex items-center justify-center"
          style="background-color: rgba(139, 92, 246, 0.1)"
        >
          <svg class="w-6 h-6" style="color: var(--primary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
            ></path>
          </svg>
        </div>
      </div>
    </div>

    <div class="card rounded-lg p-4">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium" style="color: var(--text-secondary)">Aujourd'hui -tr</p>
          <p class="text-2xl font-bold mt-1" id="todayCount">8</p>
        </div>
        <div
          class="w-12 h-12 rounded-lg flex items-center justify-center"
          style="background-color: rgba(16, 185, 129, 0.1)"
        >
          <svg
            class="w-6 h-6"
            style="color: var(--status-validated)"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
            ></path>
          </svg>
        </div>
      </div>
    </div>

    <div class="card rounded-lg p-4">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium" style="color: var(--text-secondary)">Total -tr</p>
          <p class="text-2xl font-bold mt-1" id="totalCount">23</p>
        </div>
        <div
          class="w-12 h-12 rounded-lg flex items-center justify-center"
          style="background-color: rgba(59, 130, 246, 0.1)"
        >
          <svg class="w-6 h-6" style="color: var(--btn-info)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
            ></path>
          </svg>
        </div>
      </div>
    </div>
  </div>

  <div v-if="this.loading">
    <skeleton-tabs />
  </div>
  <div v-else>
    <div>
      <div class="flex items-center gap-2 float-end">
        <button class="btn btn-outline-dark px-4 py-2 text-sm" onclick="markAllAsRead()">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
            ></path>
          </svg>
          Tout marquer comme lu -tr
        </button>
        <button class="btn btn-ghost-primary px-3 py-2" onclick="deleteAllRead()">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
            ></path>
          </svg>
        </button>
      </div>
    </div>
  </div>

  <!--<div :class="this.loading === true ? 'block-grid' : ''">
    <div v-if="this.loading" class="overlay">
      <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1000">
        <div class="spinner-border text-primary" role="status"></div>
        <span class="txt-overlay">{{ this.translation.loading }}</span>
      </div>
    </div>

    <div v-if="this.notifications.length > 0">
      <div class="btn-group">
        <button
          class="btn btn-secondary dropdown-toggle"
          type="button"
          data-bs-toggle="dropdown"
          data-bs-auto-close="true"
          aria-expanded="false"
        >
          {{ this.translation.nb_notifification_show_start }} {{ this.cLimit }}
          {{ this.translation.nb_notifification_show_end }}
        </button>
        <ul class="dropdown-menu">
          <li v-for="i in this.listLimit">
            <a class="dropdown-item" href="#" :data-limit="i" @click="this.changeLimit(i)">{{ i }}</a>
          </li>
        </ul>
      </div>
      <div v-if="onlyNotRead === 1" class="btn btn-secondary ms-2" @click="this.loadOnlyNotRead()">
        {{ this.translation.onlyNotRead }}
      </div>
      <div v-else class="btn btn-secondary ms-2" @click="this.loadAll()">{{ this.translation.all }}</div>
      <div class="btn btn-secondary ms-2" @click="this.readAll()" :class="allReadBtn ? '' : 'disabled'">
        {{ this.translation.readAll }}
      </div>

      <div class="clearfix"></div>

      <div v-if="allReadSuccess" class="alert alert-success mt-4">{{ this.translation.allSuccess }}</div>

      <div class="mt-4">
        <div v-for="notification in this.notifications">
          <div
            :id="'notification-' + notification.id"
            class="card bg-opacity-10 p-2 shadow-sm rounded-end mb-2"
            :class="this.cssClass(notification)"
            @mouseover="mouseover(notification.id)"
          >
            <div class="card-body">
              <div class="float-end">
                <i>{{ this.formatDate(notification.createdAt.timestamp) }}</i>
              </div>
              <h5 class="card-title">{{ notification.title }}</h5>
              <span class="card-text" v-html="notification.content"></span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-else class="mt-4">
      <button v-if="!loading" class="btn btn-secondary disabled">
        {{ this.translation.nb_notifification_show_start }} {{ this.cLimit }}
        {{ this.translation.nb_notifification_show_end }}
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
  </div>-->
</template>

<style scoped></style>
