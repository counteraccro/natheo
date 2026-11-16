<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * En-tête de la page ouverte : image bord à bord, fil d'Ariane, brouillon, titre, auteur et dates, tags, édition
 */
import { computed, toRef } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useBreadcrumb } from '@/vue/Composables/Front/useBreadcrumb';
import { useEditLink } from '@/vue/Composables/Front/useEditLink';
import { useFormat } from '@/vue/Composables/Front/useFormat';
import FrontIcon from '@/vue/Components/Front/Global/FrontIcon.vue';
import TagList from '@/vue/Components/Front/StormFront/Thread/TagList.vue';
import type { Page } from '@/ts/Front/Api/Page.type';

const props = defineProps<{ page: Page; readingTime: number | null }>();
const { datas, translate } = useFrontContext();
const breadcrumb = useBreadcrumb(toRef(props, 'page'));
const editLink = useEditLink();
const format = useFormat();
const updatedAt = computed(() => format.date(props.page.updatedAt));
const article = computed(() => props.page.category !== 'page');
</script>

<template>
  <header>
    <img
      v-if="page.headerImage"
      :src="page.headerImage"
      :alt="translate.main.headerImgAlt"
      class="-mx-[clamp(1.25rem,4vw,4.5rem)] aspect-video w-[calc(100%+2*clamp(1.25rem,4vw,4.5rem))] max-w-none object-cover md:aspect-21/9"
    />
    <div class="max-w-190 pt-12">
      <nav v-if="breadcrumb.length > 1" :aria-label="translate.main.breadcrumb">
        <ol class="mb-6 flex flex-wrap gap-1.5 text-[0.9rem] text-fg-muted">
          <li v-for="(item, index) in breadcrumb" :key="index" class="flex gap-1.5">
            <template v-if="item.url">
              <a :href="item.url" class="text-fg-muted">{{ item.label }}</a>
              <span aria-hidden="true">/</span>
            </template>
            <span v-else aria-current="page">{{ item.label }}</span>
          </li>
        </ol>
      </nav>

      <p
        v-if="datas.preview"
        role="status"
        class="mb-6 rounded-sf border-l-4 border-notice-line bg-notice px-4 py-3 text-[0.95rem] font-medium text-on-notice"
      >
        {{ translate.main.articleFooter.infoDraft }}
      </p>

      <h1
        id="page-title"
        tabindex="-1"
        class="text-[clamp(2.5rem,4.6vw,4rem)] leading-[1.02] tracking-[-0.04em] text-balance outline-none"
      >
        {{ page.title }}
      </h1>

      <div class="mt-5 text-[0.98rem] text-fg-muted">
        <p>
          <template v-if="article && page.author">
            {{ translate.main.listing.by }} <strong class="font-semibold text-fg">{{ page.author.name }}</strong>.
          </template>
          <template v-if="updatedAt">
            {{ translate.main.updatedAt }} <time :datetime="page.updatedAt ?? undefined">{{ updatedAt }}</time>.
          </template>
        </p>
        <p v-if="article">
          <template v-if="readingTime">{{ format.plural(readingTime, translate.main.readingTime) }}, </template>
          {{ format.plural(page.reads, translate.main.articleFooter.reads) }}.
        </p>
      </div>

      <TagList :tags="page.tags" class="mt-4" />

      <a
        v-if="editLink"
        :href="editLink"
        class="mt-6 inline-flex min-h-10 items-center gap-2 rounded-sf px-3.5 text-[0.92rem] font-semibold text-fg no-underline shadow-[inset_0_0_0_1.5px_currentColor]"
      >
        <FrontIcon name="edit" class="size-4.5" />
        {{ translate.main.editLink }}
      </a>
    </div>
  </header>
</template>
