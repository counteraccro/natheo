<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Permet d'afficher la liste des notifications
 */
import { defineComponent, type PropType } from 'vue';
import axios from 'axios';
import SkeletonCardStat from '@/vue/Components/Skeleton/CardStat.vue';
import SkeletonTabs from '@/vue/Components/Skeleton/Tabs.vue';
import Notification from '@/vue/Components/Notification/Notification.vue';
import type { NotificationItem } from '@/ts/Notification/Notification.type';
import type {
  NotificationCategory,
  NotificationChecked,
  NotificationListCsrfTokens,
  NotificationListTranslate,
  NotificationListUrls,
} from '@/ts/Notification/NotificationList.type';
import Modal from '@/vue/Components/Global/Modal.vue';
import { toastSuccess, toastError, toastRequestError } from '@/vue/Composables/Global/toast';
import { withCsrf } from '@/vue/Composables/Admin/csrf';

export default defineComponent({
  name: 'NotificationList',
  components: { Modal, Notification, SkeletonTabs, SkeletonCardStat },
  props: {
    urls: { type: Object as PropType<NotificationListUrls>, required: true },
    page: { type: Number, required: true },
    limit: { type: Number, required: true },
    translation: { type: Object as PropType<NotificationListTranslate>, required: true },
    categories: { type: Object as PropType<Record<string, NotificationCategory>>, required: true },
    csrf_tokens: { type: Object as PropType<NotificationListCsrfTokens>, required: true },
  },
  data() {
    return {
      notifications: [] as NotificationItem[],
      notificationsChecked: {} as Record<string, NotificationChecked>,
      checkedAll: false,
      loading: true,
      loadingStat: true,
      modale: {
        showModale: false,
        msgConfirm: '',
      },
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
  computed: {
    /**
     * Au moins une notification est cochée
     */
    hasNotificationsChecked(): boolean {
      return Object.keys(this.notificationsChecked).length > 0;
    },

    /**
     * Au moins une notification cochée est lue
     */
    hasAtLeastOneRead(): boolean {
      return Object.values(this.notificationsChecked).some((notif) => notif.isRead === true);
    },
  },
  methods: {
    /**
     * Chargement des statistiques
     */
    loadStatistic(): void {
      axios
        .get(this.urls.statistics)
        .then((response) => {
          this.stats.nb_noRead = response.data.nb_noRead;
          this.stats.nb_today = response.data.nb_today;
          this.stats.nb_total = response.data.nb_total;
        })
        .catch((error) => {
          toastRequestError(error);
        })
        .finally(() => {
          this.loadingStat = false;
        });
    },

    /**
     * Chargement des données
     * @param page
     * @param limit
     * @param onlyNotRead
     */
    loadData(page: number, limit: number, onlyNotRead: number): void {
      axios
        .get(this.urls.list + '/' + page + '/' + limit + '/' + onlyNotRead)
        .then((response) => {
          this.notifications = response.data.notifications;
          this.checkedAll = true;
        })
        .catch((error) => {
          toastRequestError(error);
        })
        .finally(() => {
          const element = document.getElementById('check-all') as HTMLInputElement | null;
          if (element) {
            element.checked = false;
          }

          this.notificationsChecked = {};
          this.checkedAll = false;
          this.loading = false;
        });
    },

    /**
     * Met à jour le tableau de notification checked
     * @param id
     * @param isRead
     * @param isChecked
     */
    updateTabNotificationChecked(id: string, isRead: string, isChecked: boolean): void {
      if (isChecked) {
        this.notificationsChecked[id] = { id: id, isRead: isRead === 'true' };
      } else {
        delete this.notificationsChecked[id];
      }
    },

    /**
     * Selectionne toutes les notifications
     * @param event
     */
    checkedAllNotification(event: Event): void {
      const target = event.target as HTMLInputElement;
      if (target.checked) {
        this.checkedAll = true;
        this.notifications.forEach((notification) => {
          this.notificationsChecked[notification.id] = { id: notification.id, isRead: notification.read };
        });
      } else {
        this.checkedAll = false;
        this.notificationsChecked = {};
      }
    },

    /**
     * Met à jour une ou plusieurs notifications
     * @param read
     */
    updateNotification(read: boolean): void {
      this.loading = true;
      axios
        .post(
          this.urls.update,
          { notifications: this.notificationsChecked, read: read },
          withCsrf(this.csrf_tokens.update)
        )
        .then((response) => {
          if (response.data.success === true) {
            toastSuccess(response.data.msg);
          } else {
            toastError(response.data.msg);
          }
        })
        .catch((error) => {
          toastRequestError(error);
        })
        .finally(() => {
          this.loadStatistic();
          this.loadData(this.page, this.limit, 0);
        });
    },

    /**
     * Supprime les notifications cochées (confirm : affiche d'abord la modale de confirmation)
     * @param confirm
     */
    deleteNotification(confirm: boolean): void {
      if (confirm) {
        this.showModal();
        const checked = Object.values(this.notificationsChecked);
        this.modale.msgConfirm = this.translation.confirmMsg
          .replace('{total}', String(checked.length))
          .replace('{noRead}', String(checked.filter((notif) => !notif.isRead).length));
      } else {
        this.loading = true;
        this.modale.showModale = false;
        axios
          .post(this.urls.delete, { notifications: this.notificationsChecked }, withCsrf(this.csrf_tokens.delete))
          .then((response) => {
            if (response.data.success === true) {
              toastSuccess(response.data.msg);
            } else {
              toastError(response.data.msg);
            }
          })
          .catch((error) => {
            toastRequestError(error);
          })
          .finally(() => {
            this.loadStatistic();
            this.loadData(this.page, this.limit, 0);
          });
      }
    },

    /**
     * Met toutes les notifications non lu en lu
     */
    readAll(): void {
      this.loading = true;
      axios
        .post(this.urls.readAll, {}, withCsrf(this.csrf_tokens.readAll))
        .then((response) => {
          if (response.data.success === true) {
            toastSuccess(response.data.msg);
          } else {
            toastError(response.data.msg);
          }
        })
        .catch((error) => {
          toastRequestError(error);
        })
        .finally(() => {
          this.loadStatistic();
          this.loadData(this.page, this.limit, 0);
        });
    },

    /**
     * Lance la purge des notifications
     */
    purge(): void {
      axios
        .post(this.urls.purge, {}, withCsrf(this.csrf_tokens.purge))
        .catch((error) => {
          toastRequestError(error);
        })
        .finally(() => {
          this.loadStatistic();
          this.loadData(this.page, this.limit, 0);
        });
    },

    /**
     * Retourne les notifications d'une catégorie
     * @param category
     */
    getNotifByCategory(category: string): NotificationItem[] {
      return this.notifications.filter((notification) => notification.category === category);
    },

    /**
     * Retourne les notifications lues ou non lues
     * @param isRead
     */
    getNotifByIsRead(isRead: boolean): NotificationItem[] {
      return this.notifications.filter((notification) => notification.read === isRead);
    },

    /**
     * Affichage la modale
     */
    showModal(): void {
      this.modale.showModale = true;
    },

    /**
     * Ferme la modale
     */
    hideModal(): void {
      this.modale.showModale = false;
    },

    /**
     * Marque une notification comme lu
     * @param id
     */
    markAsRead(id: number): void {
      this.stats.nb_noRead--;

      const tab: Record<string, NotificationChecked> = { [id]: { id: id, isRead: false } };

      axios
        .post(this.urls.update, { notifications: tab, read: true }, withCsrf(this.csrf_tokens.update))
        .then((response) => {
          if (response.data.success === true) {
            toastSuccess(response.data.msg);
          } else {
            toastError(response.data.msg);
          }
        })
        .catch((error) => {
          toastRequestError(error);
        });
    },

    /**
     * Supprime une notification
     * @param id
     * @param confirm
     */
    deleteNotif(id: number, confirm: boolean): void {
      if (confirm) {
        this.notificationsChecked = {};
        this.notificationsChecked[id] = { id: id, isRead: true };
        this.showModal();
        this.modale.msgConfirm = this.translation.confirmSingleDelete;
      }
    },
  },
});
</script>

<template>
  <div v-if="loadingStat">
    <SkeletonCardStat />
  </div>
  <div v-else class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="card rounded-lg p-4">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-fg-muted">{{ translation.statNonLu }}</p>
          <p class="text-2xl font-bold mt-1" id="unreadCount">{{ stats.nb_noRead }}</p>
        </div>
        <div
          class="w-12 h-12 rounded-lg flex items-center justify-center"
          style="background-color: rgba(139, 92, 246, 0.1)"
        >
          <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
          <p class="text-sm font-medium text-fg-muted">{{ translation.statToday }}</p>
          <p class="text-2xl font-bold mt-1" id="todayCount">{{ stats.nb_today }}</p>
        </div>
        <div
          class="w-12 h-12 rounded-lg flex items-center justify-center"
          style="background-color: rgba(16, 185, 129, 0.1)"
        >
          <svg class="w-6 h-6 text-status-validated" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
          <p class="text-sm font-medium text-fg-muted">{{ translation.statAll }}</p>
          <p class="text-2xl font-bold mt-1" id="totalCount">{{ stats.nb_total }}</p>
        </div>
        <div
          class="w-12 h-12 rounded-lg flex items-center justify-center"
          style="background-color: rgba(59, 130, 246, 0.1)"
        >
          <svg class="w-6 h-6 text-btn-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

  <div v-if="loading">
    <SkeletonTabs />
  </div>
  <div v-else>
    <div class="flex items-end gap-2 flex-row-reverse">
      <button class="btn btn-outline-dark px-4 py-2 text-sm" @click="readAll" :disabled="stats.nb_noRead === 0">
        <svg
          class="icon"
          aria-hidden="true"
          xmlns="http://www.w3.org/2000/svg"
          width="24"
          height="24"
          fill="none"
          viewBox="0 0 24 24"
        >
          <path
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M3 15v3c0 .5523.44772 1 1 1h10.5M3 15v-4m0 4h11M3 11V6c0-.55228.44772-1 1-1h16c.5523 0 1 .44772 1 1v5M3 11h18m0 0v1M8 11v8m4-8v8m4-8v2m1 4h2m0 0h2m-2 0v2m0-2v-2"
          />
        </svg>

        {{ translation.readAll }}
      </button>

      <div v-if="hasNotificationsChecked">
        <button
          v-if="!hasAtLeastOneRead"
          class="btn btn-outline-dark px-4 py-2 text-sm me-2"
          @click="updateNotification(true)"
        >
          <svg
            class="icon"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            fill="none"
            viewBox="0 0 24 24"
          >
            <path
              stroke="currentColor"
              stroke-linecap="round"
              stroke-width="2"
              d="m3.5 5.5 7.893 6.036a1 1 0 0 0 1.214 0L20.5 5.5M4 19h16a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Z"
            />
          </svg>

          {{ translation.read }}
        </button>
        <button v-else class="btn btn-outline-dark px-4 py-2 text-sm me-2" @click="updateNotification(false)">
          <svg
            class="icon"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            fill="none"
            viewBox="0 0 24 24"
          >
            <path
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M21 8v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8m18 0-8.029-4.46a2 2 0 0 0-1.942 0L3 8m18 0-9 6.5L3 8"
            />
          </svg>

          {{ translation.noRead }}
        </button>

        <button class="btn btn-outline-dark px-4 py-2 text-sm" @click="deleteNotification(true)">
          <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
            ></path>
          </svg>
          {{ translation.deleteSelected }}
        </button>
      </div>
    </div>
  </div>

  <div v-show="!loading" class="card rounded-lg mb-6 mt-3">
    <div class="border-b border-default border-line">
      <ul
        class="flex flex-wrap -mb-px text-sm font-medium text-center px-4 sm:px-6"
        id="default-tab"
        data-tabs-toggle="#default-tab-content"
        data-tabs-active-classes="text-primary border-primary"
        role="tablist"
      >
        <li class="me-2" role="presentation">
          <input
            type="checkbox"
            class="form-check-input ms-3 no-control"
            id="check-all"
            @change="checkedAllNotification"
          />
          <button
            class="inline-block p-3 border-b-2 hover:text-fg-brand hover:border-brand border-primary cursor-pointer"
            id="all-tab"
            style="margin-bottom: -1px"
            data-tabs-target="#all"
            type="button"
            role="tab"
            aria-controls="all"
            aria-selected="false"
          >
            {{ translation.all }}
          </button>
        </li>
        <li class="me-2" role="presentation">
          <button
            class="inline-block border-b-2 hover:text-fg-brand hover:border-brand border-primary cursor-pointer"
            :class="stats.nb_noRead ? 'p-[0.67em]' : 'p-3'"
            id="not-read-tab"
            style="margin-bottom: -1px"
            data-tabs-target="#not-read"
            type="button"
            role="tab"
            aria-controls="not-read"
            aria-selected="true"
          >
            {{ translation.onlyNotRead }}
            <span v-if="stats.nb_noRead > 0" class="ms-3 badge badge-primary"> {{ stats.nb_noRead }} </span>
          </button>
        </li>
        <li v-for="category in categories" role="presentation" class="me-2">
          <button
            class="inline-block p-3 border-b-2 hover:text-fg-brand hover:border-brand cursor-pointer"
            :id="category.id + '-tab'"
            style="margin-bottom: -1px"
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
          v-if="notifications.length > 0"
          v-for="notification in notifications"
          :translation="translation.notification"
          :notification="notification"
          :render="'all'"
          @check-notification="updateTabNotificationChecked"
          @mark-as-read="markAsRead"
          @delete="deleteNotif"
          :checked="checkedAll"
        />

        <div v-else class="text-sm text-fg-muted p-4 sm:p-6 flex gap-1">
          <svg
            class="w-5 h-5"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            fill="none"
            viewBox="0 0 24 24"
          >
            <path
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M10 11h2v5m-2 0h4m-2.592-8.5h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
            />
          </svg>

          {{ translation.empty }}
        </div>
      </div>
      <div class="rounded-base bg-neutral-secondary-soft" id="not-read" role="tabpanel" aria-labelledby="not-read-tab">
        <notification
          v-if="getNotifByIsRead(false).length > 0"
          v-for="notification in getNotifByIsRead(false)"
          :translation="translation.notification"
          :notification="notification"
          @mark-as-read="markAsRead"
          @delete="deleteNotif"
        />
        <div v-else class="text-sm text-fg-muted p-4 sm:p-6 flex gap-1">
          <svg
            class="w-5 h-5"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            fill="none"
            viewBox="0 0 24 24"
          >
            <path
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M10 11h2v5m-2 0h4m-2.592-8.5h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
            />
          </svg>

          {{ translation.empty }}
        </div>
      </div>
      <div
        v-for="category in categories"
        class="hidden rounded-base bg-neutral-secondary-soft"
        :id="category.id"
        role="tabpanel"
        :aria-labelledby="category.id + '-tab'"
      >
        <notification
          v-if="getNotifByCategory(category.id).length > 0"
          v-for="notification in getNotifByCategory(category.id)"
          :translation="translation.notification"
          :notification="notification"
          @mark-as-read="markAsRead"
          @delete="deleteNotif"
        />
        <div v-else class="text-sm text-fg-muted p-4 sm:p-6 flex gap-1">
          <svg
            class="w-5 h-5"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            fill="none"
            viewBox="0 0 24 24"
          >
            <path
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M10 11h2v5m-2 0h4m-2.592-8.5h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
            />
          </svg>

          {{ translation.empty }}
        </div>
      </div>
    </div>
  </div>

  <modal
    :id="'confirm-modale-notification'"
    :show="modale.showModale"
    @close-modal="hideModal"
    :option-show-close-btn="false"
  >
    <template #icon>
      <svg class="h-6 w-6 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <path
          stroke="currentColor"
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M12 13V8m0 8h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
        />
      </svg>
    </template>
    <template #title> {{ translation.confirmTitle }} </template>
    <template #body>
      <div v-html="modale.msgConfirm"></div>
    </template>
    <template #footer>
      <button type="button" class="btn btn-outline-dark btn-sm" @click="hideModal()">
        <svg
          class="icon"
          aria-hidden="true"
          xmlns="http://www.w3.org/2000/svg"
          width="24"
          height="24"
          fill="none"
          viewBox="0 0 24 24"
        >
          <path
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="m15 9-6 6m0-6 6 6m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
          />
        </svg>

        {{ translation.confirmBtnNo }}
      </button>
      <button type="button" class="btn btn-danger btn-sm" @click="deleteNotification(false)">
        <svg
          class="icon"
          aria-hidden="true"
          xmlns="http://www.w3.org/2000/svg"
          width="24"
          height="24"
          fill="none"
          viewBox="0 0 24 24"
        >
          <path
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M8.5 11.5 11 14l4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
          />
        </svg>
        {{ translation.confirmBtnOK }}
      </button>
    </template>
  </modal>
</template>

<style scoped></style>
