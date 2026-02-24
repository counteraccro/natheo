<script lang="ts">
import { defineComponent, type PropType } from 'vue';
import NewFaq from '@/vue/Components/Faq/NewFaq.vue';
import EditFaq from '@/vue/Components/Faq/EditFaq.vue';

type TranslateRecord = { [key: string]: string | TranslateRecord };

export default defineComponent({
  name: 'Faq',
  components: { EditFaq, NewFaq },
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
  <edit-faq v-else :translate="getSubTranslate('editFaq')" :locales="locales" :urls="urls" :id="id" />
</template>

<style scoped></style>
