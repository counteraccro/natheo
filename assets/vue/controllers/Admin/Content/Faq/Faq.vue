<script lang="ts">
import { defineComponent, type PropType } from 'vue';
import NewFaq from '@/vue/Components/FAQ/NewFaq.vue';

type TranslateRecord = { [key: string]: string | TranslateRecord };

export default defineComponent({
  name: 'Faq',
  components: { NewFaq },
  props: {
    urls: { type: Object as PropType<Record<string, string>>, required: true },
    translate: { type: Object as PropType<TranslateRecord>, required: true },
    locales: { type: Object as PropType<Record<string, string>>, required: true },
    id: Number,
  },
  data() {
    return {
      loading: true,
    };
  },
  mounted(): any {},
  methods: {
    getSubTranslate(key: string): TranslateRecord {
      return this.translate[key] as TranslateRecord;
    },
  },
});
</script>

<template>
  <!-- Nouvelle FAQ -->
  <new-faq v-if="id === null" :translate="getSubTranslate('newFaq')" :urls="urls" />
</template>

<style scoped></style>
