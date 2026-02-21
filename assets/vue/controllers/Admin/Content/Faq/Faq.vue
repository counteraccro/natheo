<script lang="ts">
import { defineComponent, type PropType } from 'vue';
import SkeletonFaq from '@/vue/Components/Skeleton/Faq.vue';

export default defineComponent({
  name: 'Faq',
  components: { SkeletonFaq },
  props: {
    urls: Object as PropType<Array<string, string>>,
    translate: Object as PropType<Array<string, string>>,
    locales: Object as PropType<Array<string, string>>,
    id: Number,
  },
  data() {
    return {
      loading: true,
    };
  },
  mounted(): any {},
  methods: {},
});
</script>

<template>
  <div v-if="loading" class="mt-5">
    <SkeletonFaq :is-new="id === null" />
  </div>

  <div class="card rounded-lg p-6 mb-4 mt-4"></div>
</template>

<style scoped></style>
