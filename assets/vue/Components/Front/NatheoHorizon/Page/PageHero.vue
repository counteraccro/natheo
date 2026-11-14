<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Bandeau de la page : fil d'Ariane, brouillon, titre, auteur, date, temps de lecture, lectures, tags, image
 */
import { computed, toRef } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useFormat } from '@/vue/Composables/Front/useFormat';
import { useBreadcrumb } from '@/vue/Composables/Front/useBreadcrumb';
import { useEditLink } from '@/vue/Composables/Front/useEditLink';
import HorizonIcon from '@/vue/Components/Front/NatheoHorizon/Layout/HorizonIcon.vue';
import type { Page } from '@/ts/Front/Api/Page.type';

const props = defineProps<{ page: Page; readingTime: number | null }>();
const { datas, translate } = useFrontContext();
const breadcrumb = useBreadcrumb(toRef(props, 'page'));
const editLink = useEditLink();

const format = useFormat();
const updatedAt = computed(() => format.date(props.page.updatedAt));
</script>

<template>
  <section class="bg-night pt-10 pb-16 text-on-night [--focus:var(--focus-on-night)]" aria-labelledby="page-title">
    <div
      class="mx-auto grid w-full max-w-page items-center gap-12 px-5"
      :class="{ 'lg:grid-cols-[minmax(0,1.1fr)_minmax(0,1fr)]': page.headerImage }"
    >
      <div>
        <nav v-if="breadcrumb.length > 1" :aria-label="translate.main.breadcrumb">
          <ol class="mb-8 flex flex-wrap gap-1.5 text-[0.92rem] text-on-night-muted">
            <li v-for="(item, index) in breadcrumb" :key="index" class="flex gap-1.5">
              <template v-if="item.url">
                <a :href="item.url" class="text-on-night-muted">{{ item.label }}</a>
                <span aria-hidden="true">/</span>
              </template>
              <span v-else aria-current="page" class="text-on-night">{{ item.label }}</span>
            </li>
          </ol>
        </nav>

        <p
          v-if="datas.preview"
          role="status"
          class="mb-6 max-w-[60ch] rounded-nh bg-notice px-4 py-3 text-[0.95rem] font-medium text-on-notice"
        >
          {{ translate.main.articleFooter.infoDraft }}
        </p>

        <h1
          id="page-title"
          class="max-w-[16ch] text-[clamp(2.4rem,5.2vw,4.25rem)] leading-[1.02] motion-safe:animate-rise"
        >
          {{ page.title }}
        </h1>

        <div
          class="mt-6 flex flex-wrap gap-x-6 gap-y-1 text-[0.98rem] text-on-night-muted motion-safe:animate-rise motion-safe:[animation-delay:80ms]"
        >
          <p v-if="page.author">
            {{ translate.main.listing.by }} <strong class="font-semibold text-on-night">{{ page.author.name }}</strong>
          </p>
          <p v-if="updatedAt">{{ translate.main.updatedAt }} {{ updatedAt }}</p>
          <p v-if="readingTime">{{ readingTime }} {{ translate.main.readingTime }}</p>
          <p>{{ page.reads }} {{ translate.main.articleFooter.statPublication }}</p>
        </div>

        <ul
          v-if="page.tags.length"
          class="mt-6 flex flex-wrap gap-2 motion-safe:animate-rise motion-safe:[animation-delay:120ms]"
          :aria-label="translate.main.tagsLabel"
        >
          <li
            v-for="tag in page.tags"
            :key="tag.id"
            class="inline-flex items-center gap-2 rounded-nh bg-white/8 px-2.5 py-1 text-[0.88rem] font-medium"
          >
            <span class="size-2.5 rounded-[2px]" :style="{ backgroundColor: tag.color }" aria-hidden="true" />
            {{ tag.label }}
          </li>
        </ul>

        <a
          v-if="editLink"
          :href="editLink"
          class="mt-7 inline-flex min-h-11 items-center gap-2 rounded-nh px-4 font-semibold text-on-night no-underline shadow-[inset_0_0_0_1.5px_rgb(201_184_255/0.55)] hover:shadow-[inset_0_0_0_1.5px_#fff]"
        >
          <HorizonIcon name="edit" class="size-5" />
          {{ translate.main.editLink }}
        </a>
      </div>

      <img
        v-if="page.headerImage"
        :src="page.headerImage"
        :alt="translate.main.headerImgAlt"
        class="aspect-4/3 w-full rounded-lg object-cover motion-safe:animate-rise motion-safe:[animation-delay:150ms]"
      />
    </div>
  </section>
</template>
