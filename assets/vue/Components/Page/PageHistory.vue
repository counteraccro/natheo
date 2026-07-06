<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { PageHistoryEntry, PageHistoryTranslate, Urls } from '@/ts/Page/type';
import axios from 'axios';
import SkeletonPageHistory from '@/vue/Components/Skeleton/Page/PageHistory.vue';

export default defineComponent({
  name: 'PageHistory',
  components: { SkeletonPageHistory },
  props: {
    id: {
      type: Number as PropType<number>,
      required: true,
    },
    urls: {
      type: Object as PropType<Urls>,
      required: true,
    },
    translate: {
      type: Object as PropType<PageHistoryTranslate>,
      required: true,
    },
    active: {
      type: Boolean as PropType<boolean>,
      required: true,
    },
  },
  emits: ['restore-history'],
  data() {
    return { loading: false, history: [] as PageHistoryEntry[] };
  },
  watch: {
    active(value: boolean) {
      if (value) {
        this.loadHistory();
      }
    },
  },

  methods: {
    /**
     * Charge l'historique
     */
    loadHistory() {
      this.loading = true;
      axios
        .get(this.urls.load_tab_history + '/' + this.id)
        .then((response) => {
          this.history = response.data.history;
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;
        });
    },

    /**
     * Restaure une page
     * @param entry
     */
    restore(entry: PageHistoryEntry) {
      this.loading = true;

      axios
        .post(this.urls.reload_page_history, {
          row_id: entry.id,
          id: this.id,
        })
        .then((response) => {
          //this.$emit('restore-history', response.data.page);
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;
        });
    },
  },
});
</script>

<template>
  <SkeletonPageHistory v-if="loading" />
  <div v-else>Tab History</div>
</template>

<style scoped></style>
