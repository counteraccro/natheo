<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Page introuvable du thème Natheo-Studio : grille de blocs dont le dernier tiers est tombé, sa place marquée « 404 »
 */
import { UiButton } from '@ui';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import type { AdminNotFoundProps } from '@/ts/Admin/Theme/AdminTheme.type';

defineProps<AdminNotFoundProps>();

const { t } = useAdminContext();
// Arrivée directe sur l'adresse : aucune page où revenir
const canGoBack = window.history.length > 1;
</script>

<template>
  <section
    class="grid min-h-[calc(100vh-60px-5rem)] place-items-center px-8 pt-12 pb-16 max-lg:px-2 max-lg:pt-6"
    aria-labelledby="not-found-title"
  >
    <div
      class="grid grid-cols-[auto_minmax(0,440px)] items-center gap-16 max-lg:grid-cols-[minmax(0,440px)] max-lg:justify-items-start max-lg:gap-9"
    >
      <div class="relative w-62 pb-19 max-lg:w-50 max-lg:pb-16.5" aria-hidden="true">
        <div class="grid grid-cols-6 gap-2.5">
          <i class="col-span-6 h-16 rounded-[10px] bg-mod-full max-lg:h-13" />
          <i class="col-span-3 h-16 rounded-[10px] bg-mod-half max-lg:h-13" />
          <i class="col-span-3 h-16 rounded-[10px] bg-mod-half max-lg:h-13" />
          <i class="col-span-2 h-16 rounded-[10px] bg-mod-third-a max-lg:h-13" />
          <i class="col-span-2 h-16 rounded-[10px] bg-mod-third-a max-lg:h-13" />
          <span
            class="col-span-2 grid h-16 place-items-center rounded-[10px] border-2 border-dashed border-line-strong bg-[color-mix(in_srgb,var(--surface)_60%,transparent)] text-xl font-[750] tracking-[0.02em] text-fg-muted tabular-nums max-lg:h-13"
          >
            404
          </span>
        </div>
        <i
          class="fallen absolute right-[-6px] bottom-0 h-16 w-[calc((100%-20px)/3)] rounded-[10px] bg-mod-third-b shadow-[0_10px_20px_-10px_rgb(0_0_0/0.35)] max-lg:h-13"
        />
      </div>

      <!-- Même retrait bas que le dessin : le texte se centre sur la grille, pas sur le bloc tombé -->
      <div class="pb-19 max-lg:pb-0">
        <h1
          id="not-found-title"
          class="m-0 mb-3 text-[32px] leading-[1.15] font-bold tracking-[-0.015em] max-lg:text-[26px]"
        >
          {{ t('admin_app.not_found.title') }}
        </h1>
        <p class="m-0 mb-2.5 max-w-[60ch] text-[15px] text-fg-muted">{{ t('admin_app.not_found.text') }}</p>
        <code
          class="mt-0.5 mb-3.5 inline-block rounded-[5px] border border-line bg-surface px-2.5 py-1 font-mono text-[13px] break-all text-fg"
        >
          {{ path }}
        </code>
        <p class="m-0 max-w-[60ch] text-sm text-fg-subtle">{{ t('admin_app.not_found.hint') }}</p>
        <div class="mt-6.5 flex flex-wrap gap-2.5">
          <UiButton variant="primary" icon="dashboard" :href="dashboardUrl">
            {{ t('admin_app.not_found.dashboard') }}
          </UiButton>
          <UiButton v-if="canGoBack" icon="chevron-left" @click="$router.back()">
            {{ t('admin_app.not_found.back') }}
          </UiButton>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
/* Décalage entre la case « 404 » et la place finale du bloc : retrait bas du dessin, 6 px à droite */
.fallen {
  --slot-x: -6px;
  --slot-y: -76px;
  transform: rotate(-14deg);
  animation: fall 0.9s 0.5s backwards;
}

@media (max-width: 1023px) {
  .fallen {
    --slot-y: -66px;
  }
}

/* Le bloc quitte sa case : chute accélérée, petit rebond, puis il se pose penché */
@keyframes fall {
  from {
    transform: translate(var(--slot-x), var(--slot-y)) rotate(0deg);
    animation-timing-function: cubic-bezier(0.55, 0, 1, 0.45);
  }
  55% {
    transform: translate(0, 0) rotate(-12deg);
    animation-timing-function: cubic-bezier(0, 0.55, 0.45, 1);
  }
  75% {
    transform: translate(0, -6px) rotate(-15deg);
    animation-timing-function: cubic-bezier(0.55, 0, 1, 0.45);
  }
  to {
    transform: translate(0, 0) rotate(-14deg);
  }
}

@media (prefers-reduced-motion: reduce) {
  .fallen {
    animation: none;
  }
}
</style>
