<script lang="ts">
import { defineComponent, type PropType } from 'vue';
import { MediaItem } from '@/ts/Mediatheque/type';
import AlertWarning from '@/vue/Components/Alert/Warning.vue';

type TranslateRecord = { [key: string]: string | TranslateRecord };

export default defineComponent({
  name: 'MediaEdit',
  components: { AlertWarning },
  props: {
    data: { type: Object as PropType<MediaItem>, required: true },
    translate: { type: Object as PropType<TranslateRecord>, required: true },
  },
  emits: ['close'],
  data() {
    return {
      save: false,
    };
  },
  computed: {},
  methods: {},
});
</script>

<template>
  <div class="info-drawer-inner">
    <!-- Header du panneau -->
    <div class="flex items-center justify-between mb-4">
      <h3 class="flex gap-1 items-center font-semibold text-sm" style="color: var(--text-primary)">
        <svg
          class="w-4 h-4 flex-shrink-0"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
          style="color: var(--text-secondary)"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
          ></path>
        </svg>
        {{ translate.title }}
      </h3>
      <button
        @click="$emit('close')"
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
      <img :src="data.thumbnail" alt="" class="w-full object-cover" style="max-height: 130px" />
    </div>

    <!-- Nom + type -->
    <p id="drawerName" class="font-semibold text-sm mb-1" style="color: var(--text-primary)">{{ data.name }}</p>

    <div class="form-group mt-4">
      <label class="form-label">{{ translate.input_label }}</label>
      <input class="form-input" type="text" :placeholder="translate.input_placeholder" />
      <span class="form-text"></span>
    </div>

    <alert-warning :text="translate.input_info" type="alert-warning-bordered" />

    <div class="flex flex-col gap-2 mt-3">
      <button
        class="btn btn-sm"
        :disabled="save"
        :class="!save ? 'btn-primary' : 'btn-success'"
        v-html="!save ? translate.btn_save : '✓ ' + translate.save_ok"
      ></button>
    </div>
  </div>
</template>

<style scoped></style>
