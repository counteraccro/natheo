<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet d'afficher le nombre de notification dans la zone admin
 */
import { defineComponent } from 'vue';
import axios from 'axios';

export default defineComponent({
  name: 'NotificationBadge',
  props: {
    url: { type: String, required: true },
  },
  data() {
    return {
      number: 0,
    };
  },
  mounted() {
    this.loadData();
  },
  methods: {
    /**
     * Chargement du nombre de notifications non lues
     */
    loadData(): void {
      axios
        .get(this.url)
        .then((response) => {
          this.number = response.data.number;
        })
        .catch((error) => {
          console.error(error);
        });
    },
  },
});
</script>

<template>
  <span v-if="number > 0" class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
</template>

<style scoped></style>
