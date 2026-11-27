<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Barre de debug (environnement dev) : bouton flottant en bas à gauche, panneau système, git, application et
 * connexion rapide avec un autre utilisateur
 */
import { ref, useId } from 'vue';
import BrandLogo from '@/vue/Components/Global/Ui/BrandLogo.vue';
import UiIcon from '@/vue/Components/Global/Ui/UiIcon.vue';
import { useUiLabels } from '@/vue/Composables/Admin/Ui/useUiLabels';
import type { DebugSection, DebugUser } from '@/ts/Admin/Layout/AdminDebugBar.type';

defineProps<{ env: string; branch?: string; sections: DebugSection[]; users?: DebugUser[] }>();

const labels = useUiLabels();
const open = ref(false);
const panelId = `ui-debug-${useId()}`;
const button = ref<HTMLButtonElement | null>(null);

const close = (): void => {
  open.value = false;
  button.value?.focus();
};
</script>

<template>
  <div class="fixed bottom-4 left-[288px] z-[85] text-sm max-lg:left-4 max-md:bottom-[72px]" @keydown.esc="close">
    <section
      v-show="open"
      :id="panelId"
      :aria-label="labels.debugBar.open"
      class="absolute bottom-[calc(100%+8px)] left-0 w-[340px] max-w-[calc(100vw-32px)] rounded-lg bg-code text-on-code shadow-[0_24px_48px_-12px_rgb(0_0_0/.55),0_0_0_1px_rgb(255_255_255/.08)]"
    >
      <header class="flex items-center gap-2.5 border-b border-white/8 py-3 pr-3 pl-3.5">
        <BrandLogo class="size-[22px]" />
        <b class="font-[650]">Nathéo</b>
        <span class="rounded-full bg-code-number px-1.5 py-px text-2xs font-bold text-on-code-number">{{ env }}</span>
        <button
          type="button"
          :aria-label="labels.debugBar.close"
          class="ml-auto grid size-[30px] cursor-pointer place-items-center rounded-sm border-0 bg-transparent text-on-code-muted hover:bg-white/8 hover:text-on-code"
          @click="close"
        >
          <UiIcon name="close" class="size-4" />
        </button>
      </header>
      <div
        v-for="section in sections"
        :key="section.title"
        class="border-t border-white/6 px-3.5 py-2.5 first-of-type:border-t-0"
      >
        <h3 class="m-0 mb-1.5 text-xs font-semibold text-on-code-muted">{{ section.title }}</h3>
        <dl class="m-0 grid grid-cols-[auto_1fr] gap-x-3 gap-y-1">
          <template v-for="entry in section.entries" :key="entry.label">
            <dt class="text-on-code-muted">{{ entry.label }}</dt>
            <dd class="m-0 truncate text-right tabular-nums">{{ entry.value }}</dd>
          </template>
        </dl>
      </div>
      <div v-if="users?.length" class="border-t border-white/6 px-3.5 py-2.5">
        <h3 class="m-0 mb-1.5 text-xs font-semibold text-on-code-muted">{{ labels.debugBar.loginAs }}</h3>
        <ul class="m-0 flex list-none flex-col gap-0.5 p-0">
          <li v-for="user in users" :key="user.href">
            <a
              :href="user.href"
              class="-mx-2 flex items-center gap-2.5 rounded-sm px-2 py-1.5 text-inherit no-underline hover:bg-white/8 focus-visible:bg-white/8"
              :aria-current="user.current || undefined"
            >
              <span
                class="grid size-[26px] shrink-0 place-items-center rounded-full bg-code-raised text-2xs font-bold text-on-code"
                aria-hidden="true"
              >
                {{ user.initials }}
              </span>
              <span class="min-w-0">
                <b class="block leading-tight font-semibold">{{ user.name }}</b>
                <small class="text-xs text-on-code-muted">{{ user.detail }}</small>
              </span>
              <span v-if="user.current" class="ml-auto text-2xs text-code-string">{{
                labels.debugBar.currentUser
              }}</span>
            </a>
          </li>
        </ul>
      </div>
    </section>
    <button
      ref="button"
      type="button"
      :aria-expanded="open"
      :aria-controls="panelId"
      class="inline-flex h-[34px] cursor-pointer items-center gap-2 rounded-full border-0 bg-code pr-3 pl-2.5 font-semibold text-on-code shadow-[0_8px_24px_rgb(0_0_0/.3)] focus-visible:outline-mod-third-b"
      @click="open = !open"
    >
      <span class="sr-only">{{ labels.debugBar.open }}</span>
      <BrandLogo class="size-[18px]" />
      <span class="rounded-full bg-code-number px-1.5 py-px text-2xs font-bold text-on-code-number">{{ env }}</span>
      <span v-if="branch" class="max-w-[220px] truncate font-medium text-on-code-muted max-md:hidden">{{
        branch
      }}</span>
    </button>
  </div>
</template>
