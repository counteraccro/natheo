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
    page: Number,
    limit: Number,
  },
  data() {
    return {
      notifications: Object,
      translation: Object,
    }
  },
  mounted() {
    this.loadData(this.page, this.limit)
  },
  methods: {
    /**
     * Chargement des données
     * @param page
     * @param limit
     */
    loadData(page, limit) {
      axios.post(this.url, {
        'page': page,
        'limit': limit
      }).then((response) => {
        this.notifications = response.data.notifications;
        this.translation = response.data.translation
      }).catch((error) => {
        console.log(error);
      }).finally();
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

        nbElement.innerHTML = parseInt(nbElement.innerHTML) - 1;
        if (nbElement.innerHTML === '0') {
          nbElement.remove();
        }
      }


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
    }
  }
}
</script>

<template>
  <div v-for="notification in this.notifications">
    <div :id="'notification-' + notification.id" class="card bg-opacity-10 p-2 shadow-sm rounded-end mb-2" :class="this.cssClass(notification)" @mouseover="mouseover(notification.id)">
      <div class="card-body">
        <h5 class="card-title">{{ notification.title }}</h5>
        <span class="card-text" v-html="notification.content"></span>
      </div>
    </div>
  </div>

</template>

<style scoped>

</style>