<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tiroir de navigation : menu de l'en-tête en accordéon, langues et déconnexion
 * (petits écrans, et tous les écrans pour un menu d'en-tête en barre latérale)
 */
import { nextTick, ref, watch } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useLanguages } from '@/vue/Composables/Front/useLanguages';
import { useMenus } from '@/vue/Composables/Front/useMenus';
import BrandLogo from '@/vue/Components/Front/NatheoHorizon/Layout/BrandLogo.vue';
import HorizonIcon from '@/vue/Components/Front/NatheoHorizon/Layout/HorizonIcon.vue';
import MenuLink from '@/vue/Components/Front/NatheoHorizon/Menu/MenuLink.vue';
import type { Menu } from '@/ts/Front/Api/Menu.type';

defineProps<{ id: string; menu: Menu | null; siteName: string }>();
const emit = defineEmits<{ closed: [] }>();
const open = defineModel<boolean>('open', { required: true });
const { urls, datas, translate } = useFrontContext();
const languages = useLanguages();
const { isActive } = useMenus();
const dialog = ref<HTMLElement | null>(null);
const closeButton = ref<HTMLButtonElement | null>(null);

function close(): void {
  open.value = false;
  emit('closed');
}

watch(open, async (value) => {
  document.body.style.overflow = value ? 'hidden' : '';
  if (value) {
    await nextTick();
    closeButton.value?.focus();
  }
});

// Échap ferme, Tab reste dans le tiroir
function onKeydown(event: KeyboardEvent): void {
  if (event.key === 'Escape') {
    close();
    return;
  }
  if (event.key !== 'Tab' || dialog.value === null) {
    return;
  }
  const focusables = [...dialog.value.querySelectorAll<HTMLElement>('a[href], button, summary')];
  const first = focusables[0];
  const last = focusables[focusables.length - 1];
  if (event.shiftKey && document.activeElement === first) {
    event.preventDefault();
    last?.focus();
  } else if (!event.shiftKey && document.activeElement === last) {
    event.preventDefault();
    first?.focus();
  }
}

const link = 'flex min-h-13 items-center gap-2.5 font-semibold text-on-night no-underline';
const subLink = 'flex min-h-11 items-center gap-2.5 font-medium text-on-night-muted no-underline aria-[current=page]:text-on-night';
</script>

<template>
  <Teleport to="body">
    <div v-if="open" class="fixed inset-0 z-70 bg-[rgb(10_5_30/0.6)]" @click="close" />
    <div
      v-if="open"
      :id="id"
      ref="dialog"
      role="dialog"
      aria-modal="true"
      :aria-label="translate.menu.headerLabel"
      class="fixed inset-y-0 right-0 z-71 flex w-[min(380px,90vw)] flex-col gap-4 overflow-y-auto bg-night px-5 py-4 text-on-night [--focus:var(--focus-on-night)]"
      @keydown="onKeydown"
    >
      <div class="flex items-center justify-between">
        <span class="inline-flex items-center gap-2.5 text-[1.3rem] font-extrabold tracking-[-0.03em]">
          <BrandLogo class="size-7.5" />
          {{ siteName }}
        </span>
        <button
          ref="closeButton"
          type="button"
          class="inline-flex size-11 cursor-pointer items-center justify-center rounded-nh hover:bg-white/10"
          @click="close"
        >
          <span class="sr-only">{{ translate.header.closeMenu }}</span>
          <HorizonIcon name="close" class="size-5.5" />
        </button>
      </div>

      <nav v-if="menu" :aria-label="translate.menu.headerLabel">
        <ul>
          <li v-for="element in menu.elements" :key="element.id" class="border-b border-night-line">
            <details v-if="element.children.length" :open="isActive(element)" class="group">
              <summary :class="link" class="cursor-pointer list-none justify-between [&::-webkit-details-marker]:hidden">
                {{ element.label }}
                <HorizonIcon name="chevron-down" class="size-5.5 transition-transform group-open:rotate-180" />
              </summary>
              <ul class="pb-2.5 pl-4">
                <template v-for="child in element.children" :key="child.id">
                  <li><MenuLink :element="child" dot="bg-mod-third" :class="subLink" /></li>
                  <li v-for="grandchild in child.children" :key="grandchild.id" class="pl-4">
                    <MenuLink :element="grandchild" dot="bg-mod-third" :class="subLink" />
                  </li>
                </template>
              </ul>
            </details>
            <MenuLink v-else :element="element" dot="bg-mod-third" :class="link" />
          </li>
        </ul>
      </nav>

      <div class="mt-auto grid gap-3">
        <div v-if="languages.length > 1" class="grid grid-cols-3 gap-1.5" role="group" :aria-label="translate.header.localeLabel">
          <a
            v-for="language in languages"
            :key="language.locale"
            :href="language.url"
            :lang="language.locale"
            :hreflang="language.locale"
            :aria-current="language.current ? 'true' : undefined"
            class="inline-flex min-h-11 items-center justify-center rounded-nh font-semibold text-on-night no-underline shadow-[inset_0_0_0_1.5px_rgb(201_184_255/0.55)] aria-[current=true]:bg-white aria-[current=true]:text-night aria-[current=true]:shadow-none"
          >
            <span class="sr-only">{{ language.label }}</span>
            <span aria-hidden="true">{{ language.locale.toUpperCase() }}</span>
          </a>
        </div>
        <a
          v-if="datas.user"
          :href="urls.logout"
          class="inline-flex min-h-11 items-center justify-center rounded-nh font-semibold text-on-night no-underline shadow-[inset_0_0_0_1.5px_rgb(201_184_255/0.55)]"
        >
          {{ translate.header.logout }} ({{ datas.user.login }})
        </a>
      </div>
    </div>
  </Teleport>
</template>
