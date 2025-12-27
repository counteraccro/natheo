<script>
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Permet d'afficher la liste des notifications
 */
import axios from 'axios';
import SkeletonCardStat from '@/vue/Components/Skeleton/CardStat.vue';
import SkeletonTabs from '@/vue/Components/Skeleton/Tabs.vue';
import Notification from '@/vue/controllers/Admin/Notification/Notification.vue';
import notification from '@/vue/controllers/Admin/Notification/Notification.vue';

export default {
  name: 'NotificationList',
  components: { Notification, SkeletonTabs, SkeletonCardStat },
  props: {
    urls: Object,
    page: Number,
    limit: Number,
    translation: Object,
    categories: Object,
  },
  data() {
    return {
      notifications: [],
      urlRead: '',
      urlReadAll: '',
      listLimit: Object,
      cLimit: this.limit,
      loading: true,
      loadingStat: true,
      locale: '',
      onlyNotRead: 1,
      allReadSuccess: false,
      allReadBtn: true,
      stats: {
        nb_noRead: 0,
        nb_today: 0,
        nb_total: 0,
      },
    };
  },
  mounted() {
    this.loading = true;
    this.purge();
  },
  methods: {
    loadStatistic() {
      axios
        .get(this.urls.statistics)
        .then((response) => {
          this.stats.nb_noRead = response.data.nb_noRead;
          this.stats.nb_today = response.data.nb_today;
          this.stats.nb_total = response.data.nb_total;
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loadingStat = false;
          this.canAllRead();
        });
    },

    /**
     * Chargement des données
     * @param page
     * @param limit
     * @param onlyNotRead
     */
    loadData(page, limit, onlyNotRead) {
      axios
        .get(this.urls.list + '/' + page + '/' + limit + '/' + onlyNotRead)
        .then((response) => {
          this.notifications = response.data.notifications;
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
        .post(this.urls.purge, {})
        .then((response) => {})
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loadStatistic();
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
     * Affiche le bon format de la date
     * @param dateString
     * @returns {string}
     */
    formatDate(dateString) {
      const date = new Date(dateString);
      return new Intl.DateTimeFormat(this.locale, { dateStyle: 'long', timeStyle: 'short' }).format(date);
    },

    /**
     * Retourne une liste de notification en fonction de sa catégorie
     * @param category
     * @returns {[]}
     */
    getNotifByCategory(category) {
      let tmp = [];
      this.notifications.forEach((notification, index) => {
        if (notification.category === category) {
          tmp.push(notification);
        }
      });
      return tmp;
    },

    /**
     * Retourne une liste de notification en fonction de sa catégorie
     * @returns {[]}
     * @param isRead
     */
    getNotifByIsRead(isRead) {
      let tmp = [];
      this.notifications.forEach((notification, index) => {
        if (notification.read === isRead) {
          tmp.push(notification);
        }
      });
      return tmp;
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
          <p class="text-sm font-medium" style="color: var(--text-secondary)">{{ this.translation.statNonLu }}</p>
          <p class="text-2xl font-bold mt-1" id="unreadCount">{{ this.stats.nb_noRead }}</p>
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
          <p class="text-sm font-medium" style="color: var(--text-secondary)">{{ this.translation.statToday }}</p>
          <p class="text-2xl font-bold mt-1" id="todayCount">{{ this.stats.nb_today }}</p>
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
          <p class="text-sm font-medium" style="color: var(--text-secondary)">{{ this.translation.statAll }}</p>
          <p class="text-2xl font-bold mt-1" id="totalCount">{{ this.stats.nb_total }}</p>
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
    <div class="flex items-end gap-2 flex-row-reverse">
      <button class="btn btn-outline-dark px-4 py-2 text-sm" onclick="markAllAsRead()">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
          ></path>
        </svg>
        {{ this.translation.all }}
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

  <div v-show="!this.loading" class="card rounded-lg mb-6 mt-3">
    <div class="border-b border-default" style="border-color: var(--border-color)">
      <ul
        class="flex flex-wrap -mb-px text-sm font-medium text-center px-4 sm:px-6"
        id="default-tab"
        data-tabs-toggle="#default-tab-content"
        data-tabs-active-classes="text-[var(--primary)] border-[var(--primary)]"
        role="tablist"
      >
        <li class="me-2" role="presentation">
          <button
            class="inline-block p-3 border-b-2 rounded-t-base border-[var(--primary)]"
            id="all-tab"
            data-tabs-target="#all"
            type="button"
            role="tab"
            aria-controls="all"
            aria-selected="false"
          >
            {{ this.translation.all }}
          </button>
        </li>
        <li class="me-2" role="presentation">
          <button
            class="inline-block border-b-2 rounded-t-base hover:text-fg-brand hover:border-brand border-[var(--primary)]"
            :class="this.stats.nb_noRead ? 'p-[0.67em]' : 'p-3'"
            id="not-read-tab"
            data-tabs-target="#not-read"
            type="button"
            role="tab"
            aria-controls="not-read"
            aria-selected="true"
          >
            {{ this.translation.onlyNotRead }}
            <span v-if="this.stats.nb_noRead > 0" class="ms-3 badge badge-primary"> {{ this.stats.nb_noRead }} </span>
          </button>
        </li>
        <li v-for="category in this.categories" role="presentation">
          <button
            class="inline-block p-3 border-b-2 rounded-t-base hover:text-fg-brand hover:border-brand"
            :id="category.id + '-tab'"
            :data-tabs-target="'#' + category.id"
            type="button"
            role="tab"
            :aria-controls="category.id"
            aria-selected="false"
          >
            {{ category.name }}
          </button>
        </li>
      </ul>
    </div>
    <div id="default-tab-content">
      <div class="hidden rounded-base bg-neutral-secondary-soft" id="all" role="tabpanel" aria-labelledby="all-tab">
        <notification
          v-if="this.notifications.length > 0"
          v-for="notification in this.notifications"
          :translation="this.translation.notification"
          :notification="notification"
        />
      </div>
      <div class="rounded-base bg-neutral-secondary-soft" id="not-read" role="tabpanel" aria-labelledby="not-read-tab">
        <notification
          v-if="this.notifications.length > 0"
          v-for="notification in this.getNotifByIsRead(false)"
          :translation="this.translation.notification"
          :notification="notification"
        />
      </div>
      <div
        v-for="category in this.categories"
        class="hidden p-4 rounded-base bg-neutral-secondary-soft"
        :id="category.id"
        role="tabpanel"
        :aria-labelledby="category.id + '-tab'"
      >
        <notification
          v-if="this.notifications.length > 0"
          v-for="notification in this.getNotifByCategory(category.id)"
          :translation="this.translation.notification"
          :notification="notification"
        />
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
