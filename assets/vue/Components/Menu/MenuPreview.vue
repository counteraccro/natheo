<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Aperçu du menu en cours d'édition au design du thème du site public, dans une iframe qui reçoit le menu à chaque
 * modification ; largeur ordinateur ou mobile, mode clair ou sombre
 */
import { defineComponent, PropType } from 'vue';
import { Menu, PageLocales } from '@/ts/Menu/Menu.type';
import { MenuPreviewPage, MenuPreviewSlugs, MenuPreviewTranslate } from '@/ts/Menu/MenuPreview.type';
import type { Menu as ApiMenu } from '@/ts/Front/Api/Menu.type';
import { toApiMenu } from '@/vue/Composables/Admin/Menu/menuPreview';
import { postMenuPreviewMessage, readMenuPreviewMessage } from '@/vue/Composables/Global/menuPreviewMessage';

/** Hauteur du cadre avant la première mesure envoyée par l'aperçu */
const MIN_HEIGHT = 120;

export default defineComponent({
  name: 'MenuPreview',
  props: {
    menu: { type: Object as PropType<Menu>, required: true },
    locale: { type: String, required: true },
    /** URL de l'aperçu par langue */
    urls: { type: Object as PropType<Record<string, string>>, required: true },
    slugs: { type: Object as PropType<MenuPreviewSlugs>, required: true },
    pages: { type: Object as PropType<Record<string, PageLocales>>, required: true },
    previewPages: { type: Object as PropType<Record<string, MenuPreviewPage>>, required: true },
    translate: { type: Object as PropType<MenuPreviewTranslate>, required: true },
  },
  data() {
    return {
      height: MIN_HEIGHT,
      mobile: false,
      dark: false,
      ready: false,
    };
  },
  computed: {
    src(): string {
      return this.urls[this.locale] ?? Object.values(this.urls)[0] ?? '';
    },
    apiMenu(): ApiMenu | null {
      return toApiMenu({
        menu: this.menu,
        locale: this.locale,
        slugs: this.slugs,
        pages: this.pages,
        previewPages: this.previewPages,
      });
    },
  },
  watch: {
    // Nouvelle langue : l'iframe se recharge et redemande le menu
    src(): void {
      this.ready = false;
    },
    apiMenu: {
      handler(): void {
        this.sendMenu();
      },
      deep: true,
    },
    dark(): void {
      this.sendScheme();
    },
  },
  mounted() {
    window.addEventListener('message', this.onMessage);
  },
  beforeUnmount() {
    window.removeEventListener('message', this.onMessage);
  },
  methods: {
    frame(): Window | null {
      return (this.$refs.frame as HTMLIFrameElement | undefined)?.contentWindow ?? null;
    },

    onMessage(event: MessageEvent): void {
      const message = readMenuPreviewMessage(event, this.frame());
      if (message?.type === 'ready') {
        this.ready = true;
        this.sendScheme();
        this.sendMenu();
      } else if (message?.type === 'height') {
        this.height = message.height;
      }
    },

    sendMenu(): void {
      if (this.ready && this.apiMenu !== null) {
        // Copie simple : un objet réactif ne passe pas par postMessage
        postMenuPreviewMessage(this.frame(), { type: 'menu', menu: JSON.parse(JSON.stringify(this.apiMenu)) });
      }
    },

    sendScheme(): void {
      if (this.ready) {
        postMenuPreviewMessage(this.frame(), { type: 'scheme', dark: this.dark });
      }
    },
  },
});
</script>

<template>
  <div class="flex flex-wrap justify-end gap-4 mb-3">
    <div class="flex gap-1" role="group" :aria-label="translate.width_label">
      <button
        type="button"
        class="btn btn-xs"
        :class="mobile ? 'btn-outline-dark' : 'btn-primary'"
        :aria-pressed="!mobile"
        @click="mobile = false"
      >
        <svg class="icon-sm" aria-hidden="true" fill="none" viewBox="0 0 24 24">
          <path
            stroke="currentColor"
            stroke-linecap="round"
            stroke-width="2"
            d="M9 16H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1h-4m-6 0v4m0-4h6m-6 4h6m-6 0H7m8 0v-4m0 4h2"
          />
        </svg>
        {{ translate.width_desktop }}
      </button>
      <button
        type="button"
        class="btn btn-xs"
        :class="mobile ? 'btn-primary' : 'btn-outline-dark'"
        :aria-pressed="mobile"
        @click="mobile = true"
      >
        <svg class="icon-sm" aria-hidden="true" fill="none" viewBox="0 0 24 24">
          <path
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M11 17h2M8 21h8a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v16a1 1 0 0 0 1 1Z"
          />
        </svg>
        {{ translate.width_mobile }}
      </button>
    </div>
    <div class="flex gap-1" role="group" :aria-label="translate.scheme_label">
      <button
        type="button"
        class="btn btn-xs"
        :class="dark ? 'btn-outline-dark' : 'btn-primary'"
        :aria-pressed="!dark"
        @click="dark = false"
      >
        <svg class="icon-sm" aria-hidden="true" fill="none" viewBox="0 0 24 24">
          <path
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 5V3m0 18v-2M7.05 7.05 5.636 5.636m12.728 12.728L16.95 16.95M5 12H3m18 0h-2M7.05 16.95l-1.414 1.414M18.364 5.636 16.95 7.05M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"
          />
        </svg>
        {{ translate.scheme_light }}
      </button>
      <button
        type="button"
        class="btn btn-xs"
        :class="dark ? 'btn-primary' : 'btn-outline-dark'"
        :aria-pressed="dark"
        @click="dark = true"
      >
        <svg class="icon-sm" aria-hidden="true" fill="none" viewBox="0 0 24 24">
          <path
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 21a9 9 0 0 1-.5-17.986V3c-.354.966-.5 1.911-.5 3a9 9 0 0 0 9 9c.239 0 .254.018.488 0A9.004 9.004 0 0 1 12 21Z"
          />
        </svg>
        {{ translate.scheme_dark }}
      </button>
    </div>
  </div>
  <div class="overflow-hidden rounded-sm border border-line bg-surface">
    <iframe
      ref="frame"
      :src="src"
      :title="translate.iframe_title"
      class="block w-full mx-auto border-0"
      :class="{ 'max-w-[390px]': mobile }"
      :style="{ height: height + 'px' }"
    ></iframe>
  </div>
</template>
