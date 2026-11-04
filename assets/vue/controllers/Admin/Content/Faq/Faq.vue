<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Gestion FAQ
 */

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
    csrf_tokens: { type: Object as PropType<{ save: string; new: string }>, required: true },
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
  <NewFaq v-if="id === null" :translate="getSubTranslate('newFaq')" :urls="urls" :csrf_token="csrf_tokens.new" />
  <EditFaq
    v-else
    :translate="getSubTranslate('editFaq')"
    :locales="locales"
    :urls="urls"
    :id="id"
    :csrf_token="csrf_tokens.save"
  />
</template>

<style scoped></style>
