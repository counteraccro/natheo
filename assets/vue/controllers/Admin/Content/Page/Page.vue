<script lang="ts">
import { defineComponent } from 'vue';
import SkeletonPageContent from '@/vue/Components/Skeleton/Page/PageContent.vue';
import SkeletonPageTag from '@/vue/Components/Skeleton/Page/PageTag.vue';
import SkeletonPageHistory from '@/vue/Components/Skeleton/Page/PageHistory.vue';
import SkeletonPageSave from '@/vue/Components/Skeleton/Page/PageSave.vue';
import SkeletonPageSEO from '@/vue/Components/Skeleton/Page/PageSEO.vue';

export default defineComponent({
  name: 'Page',
  components: { SkeletonPageSEO, SkeletonPageSave, SkeletonPageHistory, SkeletonPageTag, SkeletonPageContent },
});
</script>

<template>
  <SkeletonPageContent />
  ---
  <SkeletonPageSEO />
  ---
  <SkeletonPageTag />
  ---
  <SkeletonPageHistory />
  ---
  <SkeletonPageSave />
</template>

<style scoped></style>
