<script>

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet d'afficher le nombre de notification dans la zone admin
 */
import axios from "axios";

export default {
    name: "NotificationBadge",
    props: {
        url: String
    },
    data() {
        return {
            translate: {},
            number: 0,
        }
    },
    mounted() {
        this.loadData();
    },
    methods: {

        /*
        Chargement des données
         */
        loadData() {
            axios.get(this.url, {}
            ).then((response) => {
                this.number = response.data.number;
            }).catch((error) => {
                console.error(error);
            }).finally();
        },
    }
}
</script>

<template>
    <span v-if="this.number > 0" id="badge-notification" class="badge-notification position-absolute start-50 badge rounded-pill bg-danger">{{ this.number }}</span>
</template>

<style scoped>

</style>