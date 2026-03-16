<script lang="ts">
import { defineComponent, type PropType } from 'vue';
import { MediaItem } from '@/ts/Mediatheque/type';

type TranslateRecord = { [key: string]: string | TranslateRecord };

export default defineComponent({
  name: 'MediaInfo',
  props: {
    data: { type: Object as PropType<MediaItem>, required: true },
    translate: { type: Object as PropType<TranslateRecord>, required: true },
  },
  emits: ['close-info'],
  data() {
    return {
      copied: false,
    };
  },
  computed: {},
  methods: {
    async copyToClipboard() {
      if (this.data.type === 'media') {
        let path = `${window.location.origin}${this.data.webPath}`;

        if (navigator.clipboard?.writeText) {
          await navigator.clipboard.writeText(path);
        } else {
          // Fallback pour HTTP / anciens navigateurs
          const textarea = document.createElement('textarea');
          textarea.value = path;
          textarea.style.position = 'fixed';
          textarea.style.opacity = '0';
          document.body.appendChild(textarea);
          textarea.focus();
          textarea.select();
          document.execCommand('copy');
          document.body.removeChild(textarea);
        }
        this.copied = true;
        setTimeout(() => (this.copied = false), 2000);
      }
    },
  },
});
</script>

<template>
  <div class="info-drawer-inner">
    <!-- Header du panneau -->
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-semibold text-sm" style="color: var(--text-primary)">{{ translate.title }}</h3>
      <button
        @click="$emit('close-info')"
        class="p-1.5 rounded-lg transition"
        style="color: var(--text-secondary)"
        onmouseover="this.style.backgroundColor = 'var(--bg-hover)'"
        onmouseout="this.style.backgroundColor = ''"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- Aperçu miniature -->
    <div id="drawerPreview" class="rounded-xl overflow-hidden mb-4" style="border: 1px solid var(--border-color)">
      <img
        v-if="data.type === 'media'"
        :src="data.thumbnail"
        alt=""
        class="w-full object-cover"
        style="max-height: 130px"
      />
    </div>

    <!-- Nom + type -->
    <p id="drawerName" class="font-semibold text-sm mb-1" style="color: var(--text-primary)">{{ data.name }}</p>
    <p id="drawerType" class="text-xs mb-4" style="color: var(--text-light)">
      <span v-if="data.type === 'media'">{{ data.extension }}</span>
    </p>

    <!-- Lignes d'info -->
    <div class="mb-4">
      <div class="info-row">
        <span class="info-label">{{ translate.taille }}</span>
        <span id="drawerSize" class="info-value"> {{ data.size }} </span>
      </div>
      <div v-if="data.type === 'media'" class="info-row">
        <span class="info-label">{{ translate.dimension }}</span>
        <span id="drawerDimensions" class="info-value"> {{ data.img_size }}</span>
      </div>
      <div class="info-row">
        <span class="info-label">{{ translate.created_at }}</span>
        <span id="drawerDate" class="info-value">{{ data.date }}</span>
      </div>
      <div v-if="data.type === 'media'" class="info-row">
        <span class="info-label">{{ translate.folder }}</span>
        <span id="drawerFolder" class="info-value" style="color: var(--primary)"> {{ data.folder }} </span>
      </div>
      <div v-if="data.type === 'media'" class="info-row">
        <span class="info-label">{{ translate.url }}</span>
        <span
          id="drawerUrl"
          class="info-value text-xs truncate block max-w-[55%]"
          style="color: var(--primary)"
          :title="data.webPath"
        >
          {{ data.webPath }}
        </span>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex flex-col gap-2">
      <button
        v-if="data.type === 'media'"
        @click="copyToClipboard()"
        class="btn btn-sm"
        :disabled="copied"
        :class="!copied ? 'btn-primary' : 'btn-success'"
        v-html="!copied ? translate.btn_copy : '✓ ' + translate.copy_ok"
      ></button>
    </div>
  </div>
</template>

<style scoped></style>
