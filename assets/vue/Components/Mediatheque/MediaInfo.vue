<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'MediaInfo',
  props: {
    data: Object,
    translate: Object,
  },
  emits: ['close-info'],
  data() {
    return {};
  },
  computed: {},
  methods: {},
});
</script>

<template>
  <div class="info-drawer-inner">
    <!-- Header du panneau -->
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-semibold text-sm" style="color: var(--text-primary)">Information</h3>
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
      <img src="" alt="" class="w-full object-cover" style="max-height: 130px" />
    </div>

    <!-- Nom + type -->
    <p id="drawerName" class="font-semibold text-sm mb-1" style="color: var(--text-primary)"></p>
    <p id="drawerType" class="text-xs mb-4" style="color: var(--text-light)"></p>

    <!-- Lignes d'info -->
    <div class="mb-4">
      <div class="info-row">
        <span class="info-label">Taille</span>
        <span id="drawerSize" class="info-value"></span>
      </div>
      <div class="info-row">
        <span class="info-label">Dimensions</span>
        <span id="drawerDimensions" class="info-value"></span>
      </div>
      <div class="info-row">
        <span class="info-label">Ajouté le</span>
        <span id="drawerDate" class="info-value"></span>
      </div>
      <div class="info-row">
        <span class="info-label">Dossier</span>
        <span id="drawerFolder" class="info-value" style="color: var(--primary)"></span>
      </div>
      <div class="info-row">
        <span class="info-label">URL</span>
        <span id="drawerUrl" class="info-value text-xs" style="color: var(--primary); word-break: break-all"></span>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex flex-col gap-2">
      <button
        class="btn-primary w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-sm font-semibold"
      >
        Copier l'URL
      </button>
      <button
        class="btn-outline w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-sm font-semibold"
      >
        Télécharger
      </button>
    </div>
  </div>
</template>

<style scoped></style>
