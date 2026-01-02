<script>
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Permet d'afficher le nombre de notification dans la zone admin
 */

export default {
  name: 'Notification',
  props: {
    notification: Object,
    translation: Object,
    render: [String, null],
    checked: [Boolean],
  },
  emits: ['check-notification', 'mark-as-read'],
  data() {
    return {
      translate: {},
      number: 0,
    };
  },
  mounted() {},
  methods: {
    /**
     * Permet de check la notification
     * @param event
     */
    checkNotification(event) {
      let target = event.target;
      this.$emit('check-notification', target.dataset.notification, target.dataset.isread, target.checked);
    },

    /**
     * Affichage de l'heure
     * @param timestamp
     * @returns {*}
     */
    relativeTime(timestamp) {
      const now = Math.floor(Date.now() / 1000);
      const diff = now - timestamp;

      const rtf = new Intl.RelativeTimeFormat('fr', { numeric: 'auto' });

      if (diff < 60) return rtf.format(-diff, 'second');
      if (diff < 3600) return rtf.format(-Math.floor(diff / 60), 'minute');
      if (diff < 86400) return rtf.format(-Math.floor(diff / 3600), 'hour');
      if (diff < 604800) return rtf.format(-Math.floor(diff / 86400), 'day');
      if (diff < 2592000) return rtf.format(-Math.floor(diff / 604800), 'week');
      if (diff < 31536000) return rtf.format(-Math.floor(diff / 2592000), 'month');
      return rtf.format(-Math.floor(diff / 31536000), 'year');
    },

    /**
     * Marque comme lu
     */
    markAsRead() {
      this.notification.read = true;
      this.$emit('mark-as-read', this.notification.id);
    },
  },
};
</script>

<template>
  <div
    class="notification-item p-4 sm:p-6"
    :class="!this.notification.read ? 'unread border-b border-[var(--border-color)]' : ''"
    style="display: block"
    data-type="comments"
    data-read="false"
  >
    <div class="flex items-start gap-3">
      <div class="checkbox-custom mt-1">
        <input
          v-if="this.render === 'all'"
          type="checkbox"
          class="form-check-input"
          id="check1"
          :data-notification="notification.id"
          :data-isRead="notification.read"
          :checked="this.checked"
          @change="this.checkNotification"
        />
      </div>

      <div class="notification-icon-wrapper notification-icon-comment">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"
          ></path>
        </svg>
      </div>

      <div class="flex-1 min-w-0">
        <div class="flex items-start justify-between gap-2 mb-1">
          <div class="flex items-center gap-2">
            <p class="font-semibold text-sm">{{ notification.title }}</p>
            <div v-if="!notification.read" class="notification-dot"></div>
          </div>

          <div class="flex justify-items-end gap-2 mb-0 items-center">
            <span class="text-xs whitespace-nowrap" style="color: var(--text-light)">{{
              this.relativeTime(notification.createdAt.timestamp)
            }}</span>
            <button class="btn-ghost p-1.5 rounded" onclick="deleteNotification(this)">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        <p class="text-sm mb-2" style="color: var(--text-secondary)" v-html="notification.content"></p>
        <div class="flex flex-wrap items-center gap-2">
          <a
            v-if="notification.category === 'comment'"
            :href="notification.tmpObjectId"
            target="_blank"
            class="text-xs font-semibold hover:underline cursor-pointer"
            style="color: var(--primary)"
          >
            {{ this.translation.seeComment }}
          </a>
          <span
            v-if="notification.category === 'comment' && notification.read === false"
            class="text-xs"
            style="color: var(--text-light)"
            >•</span
          >
          <button
            v-if="notification.read === false"
            class="text-xs hover:underline cursor-pointer"
            style="color: var(--text-secondary)"
            @click="markAsRead()"
          >
            {{ this.translation.read }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Notification Item */
.notification-item {
  transition: all 0.2s ease;
  border-left: 3px solid transparent;
}

.notification-item:hover {
  background-color: var(--bg-hover);
}

.notification-item.unread {
  background-color: var(--primary-lighter);
  border-left-color: var(--primary);
}

[data-theme-mode='dark'] .notification-item.unread {
  background-color: rgba(139, 92, 246, 0.1);
}

.notification-dot {
  width: 8px;
  height: 8px;
  background-color: var(--primary);
  border-radius: 50%;
}

.notification-icon-wrapper {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.notification-icon-success {
  background-color: rgba(16, 185, 129, 0.1);
  color: var(--status-validated);
}

.notification-icon-info {
  background-color: rgba(59, 130, 246, 0.1);
  color: var(--btn-info);
}

.notification-icon-warning {
  background-color: rgba(245, 158, 11, 0.1);
  color: var(--btn-warning);
}

.notification-icon-danger {
  background-color: rgba(239, 68, 68, 0.1);
  color: var(--btn-danger);
}

.notification-icon-comment {
  background-color: rgba(139, 92, 246, 0.1);
  color: var(--primary);
}
</style>
