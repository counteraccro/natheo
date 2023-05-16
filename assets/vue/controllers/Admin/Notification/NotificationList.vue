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
            translation: Object
        }
    },
    mounted() {
      this.loadData(this.page, this.limit)
    },
    methods: {
        loadData(page, limit) {
            axios.post(this.url, {
                'page' : page,
                'limit' : limit
            }).then((response) => {
                this.notifications = response.data.notifications;
                this.translation = response.data.translation
            }).catch((error) => {
                console.log(error);
            }).finally();
        }
    }
}
</script>

<template>

</template>

<style scoped>

</style>