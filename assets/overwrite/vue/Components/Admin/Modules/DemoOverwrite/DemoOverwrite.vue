<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Tutoriel de la surcharge : opération de l'API, écran ou composant remplacé, écran ajouté. Les blocs de code
 * affichent les fichiers réels de l'exemple
 */
import { computed, onMounted, ref } from 'vue';
import { AdminPageHeader, UiAlert, UiBadge, UiSkeleton } from '@ui';
import DemoOverwriteCode from '@admin/Modules/DemoOverwrite/DemoOverwriteCode.vue';
import DemoOverwriteText from '@admin/Modules/DemoOverwrite/DemoOverwriteText.vue';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import type { Info } from '@/ts/Global/Api/Info.type';
import infoProviderSource from '/src/Overwrite/Api/State/Provider/System/InfoProvider.php?raw';
import overwriteConfigSource from '/config/cms/overwrite.yaml?raw';
import moduleSource from '/assets/overwrite/vue/Components/Admin/Modules/DemoOverwrite/module.ts?raw';
import moduleClassSource from '/src/Overwrite/Module/DemoOverwriteModule.php?raw';

interface TutorialStep {
  title: string;
  texts: string[];
  code?: { file: string; source: string };
}

interface TutorialPart {
  id: string;
  title: string;
  intro: string;
  steps: TutorialStep[];
}

const { api, menu, t } = useAdminContext();

const info = ref<Info | null>(null);
const failed = ref(false);
const icon = computed(() => menu.items.value.find((item) => item.id === 'demo_overwrite')?.icon);
const extra = computed(() => (info.value?.extra ? JSON.stringify(info.value.extra, null, 2) : null));

const parts = computed<TutorialPart[]>(() => [
  {
    id: 'api',
    title: t('demo_overwrite.tutorial.api_title'),
    intro: t('demo_overwrite.tutorial.api_intro'),
    steps: [
      { title: t('demo_overwrite.tutorial.api_step1_title'), texts: [t('demo_overwrite.tutorial.api_step1_text')] },
      {
        title: t('demo_overwrite.tutorial.api_step2_title'),
        texts: [t('demo_overwrite.tutorial.api_step2_text'), t('demo_overwrite.tutorial.api_step2_processor')],
        code: { file: 'src/Overwrite/Api/State/Provider/System/InfoProvider.php', source: infoProviderSource },
      },
      {
        title: t('demo_overwrite.tutorial.api_step3_title'),
        texts: [t('demo_overwrite.tutorial.api_step3_text'), t('demo_overwrite.tutorial.api_step3_errors')],
        code: { file: 'config/cms/overwrite.yaml', source: overwriteConfigSource },
      },
    ],
  },
  {
    id: 'screen',
    title: t('demo_overwrite.tutorial.screen_title'),
    intro: t('demo_overwrite.tutorial.screen_text_1'),
    steps: [],
  },
  {
    id: 'module',
    title: t('demo_overwrite.tutorial.module_title'),
    intro: t('demo_overwrite.tutorial.module_intro'),
    steps: [
      {
        title: t('demo_overwrite.tutorial.module_step1_title'),
        texts: [t('demo_overwrite.tutorial.module_step1_text')],
        code: { file: 'assets/overwrite/vue/Components/Admin/Modules/DemoOverwrite/module.ts', source: moduleSource },
      },
      {
        title: t('demo_overwrite.tutorial.module_step2_title'),
        texts: [t('demo_overwrite.tutorial.module_step2_text')],
        code: { file: 'src/Overwrite/Module/DemoOverwriteModule.php', source: moduleClassSource },
      },
      {
        title: t('demo_overwrite.tutorial.module_step3_title'),
        texts: [t('demo_overwrite.tutorial.module_step3_text')],
      },
    ],
  },
]);

const summary = computed(() => [
  { id: 'intro', title: t('demo_overwrite.tutorial.intro_title') },
  { id: 'live', title: t('demo_overwrite.tutorial.live_title') },
  ...parts.value.map((part, index) => ({ id: part.id, title: `${index + 1}. ${part.title}` })),
  { id: 'remove', title: t('demo_overwrite.tutorial.remove_title') },
]);

const BUTTON_PATHS = [
  'assets/vue/Components/Admin/Ui/Form/UiButton.vue',
  'assets/overwrite/vue/Components/Admin/Ui/Form/UiButton.vue',
].join('\n');

/**
 * Fait défiler jusqu'à une partie, sans passer par le routeur
 * @param id
 */
const scrollTo = (id: string): void => {
  document.getElementById(`demo-overwrite-${id}`)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

onMounted(async () => {
  try {
    info.value = await api.get<Info>('/info');
  } catch {
    failed.value = true;
  }
});

const PANEL = 'scroll-mt-24 rounded-lg border border-line bg-bg shadow-panel';
const PANEL_HEAD = 'flex flex-wrap items-center gap-3 border-b border-line px-6 py-4';
const PANEL_TITLE = 'm-0 text-md font-[650]';
const PANEL_BODY = 'flex flex-col gap-4 px-6 py-5';
</script>

<template>
  <div class="flex flex-col gap-6">
    <AdminPageHeader
      :title="t('demo_overwrite.screen.title')"
      :description="t('demo_overwrite.screen.description')"
      :icon-path="icon"
    />

    <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,880px)_240px]">
      <article class="flex min-w-0 flex-col gap-6">
        <section id="demo-overwrite-intro" :class="PANEL">
          <header :class="PANEL_HEAD">
            <h2 :class="PANEL_TITLE">{{ t('demo_overwrite.tutorial.intro_title') }}</h2>
          </header>
          <div :class="PANEL_BODY">
            <DemoOverwriteText :text="t('demo_overwrite.tutorial.intro_text_1')" />
            <DemoOverwriteText :text="t('demo_overwrite.tutorial.intro_text_2')" />
            <DemoOverwriteText :text="t('demo_overwrite.tutorial.intro_text_3')" />
          </div>
        </section>

        <section id="demo-overwrite-live" :class="PANEL">
          <header :class="PANEL_HEAD">
            <h2 :class="PANEL_TITLE">{{ t('demo_overwrite.tutorial.live_title') }}</h2>
            <UiBadge v-if="info" :tone="info.extra ? 'success' : 'neutral'">
              {{
                info.extra ? t('demo_overwrite.tutorial.status_active') : t('demo_overwrite.tutorial.status_inactive')
              }}
            </UiBadge>
          </header>
          <div :class="PANEL_BODY">
            <DemoOverwriteText :text="t('demo_overwrite.tutorial.live_text')" />
            <UiAlert v-if="failed" variant="danger">{{ t('demo_overwrite.tutorial.error') }}</UiAlert>
            <UiSkeleton v-else-if="!info" variant="text" :lines="4" />
            <template v-else>
              <dl class="m-0 grid grid-cols-[max-content_minmax(0,1fr)] gap-x-8 gap-y-2.5">
                <dt class="text-fg-muted">{{ t('demo_overwrite.tutorial.field_site_name') }}</dt>
                <dd class="m-0 font-medium">{{ info.siteName }}</dd>
                <dt class="text-fg-muted">{{ t('demo_overwrite.tutorial.field_natheo_version') }}</dt>
                <dd class="m-0 font-medium tabular-nums">{{ info.natheoVersion }}</dd>
                <dt class="text-fg-muted">{{ t('demo_overwrite.tutorial.field_api_version') }}</dt>
                <dd class="m-0 font-medium tabular-nums">{{ info.apiVersion }}</dd>
              </dl>
              <DemoOverwriteCode v-if="extra" :file="t('demo_overwrite.tutorial.field_extra')" :code="extra" />
              <UiAlert v-else variant="info">{{ t('demo_overwrite.tutorial.inactive_text') }}</UiAlert>
            </template>
          </div>
        </section>

        <section v-for="(part, index) in parts" :id="`demo-overwrite-${part.id}`" :key="part.id" :class="PANEL">
          <header :class="PANEL_HEAD">
            <span
              class="grid size-7 shrink-0 place-items-center rounded-full bg-primary-soft text-sm font-bold text-on-primary-soft tabular-nums"
              >{{ index + 1 }}</span
            >
            <h2 :class="PANEL_TITLE">{{ part.title }}</h2>
          </header>
          <div :class="PANEL_BODY">
            <DemoOverwriteText :text="part.intro" />

            <template v-if="part.id === 'screen'">
              <DemoOverwriteCode file="assets/" :code="BUTTON_PATHS" />
              <DemoOverwriteText :text="t('demo_overwrite.tutorial.screen_text_2')" />
              <DemoOverwriteText :text="t('demo_overwrite.tutorial.screen_text_3')" />
              <UiAlert variant="info">{{ t('demo_overwrite.tutorial.screen_note') }}</UiAlert>
            </template>

            <ol v-if="part.steps.length > 0" class="m-0 flex list-none flex-col gap-6 p-0">
              <li
                v-for="(step, stepIndex) in part.steps"
                :key="step.title"
                class="flex flex-col gap-3 border-l-2 border-line pl-5"
              >
                <div>
                  <p class="m-0 text-xs font-semibold tracking-[0.04em] text-fg-subtle uppercase">
                    {{ t('demo_overwrite.tutorial.step', { number: stepIndex + 1 }) }}
                  </p>
                  <h3 class="m-0 mt-0.5 text-base font-[650]">{{ step.title }}</h3>
                </div>
                <DemoOverwriteText v-for="text in step.texts" :key="text" :text="text" />
                <DemoOverwriteCode v-if="step.code" :file="step.code.file" :code="step.code.source" />
              </li>
            </ol>
          </div>
        </section>

        <section id="demo-overwrite-remove" :class="PANEL">
          <header :class="PANEL_HEAD">
            <h2 :class="PANEL_TITLE">{{ t('demo_overwrite.tutorial.remove_title') }}</h2>
          </header>
          <div :class="PANEL_BODY">
            <DemoOverwriteText :text="t('demo_overwrite.tutorial.remove_text')" />
          </div>
        </section>
      </article>

      <nav
        class="sticky top-24 rounded-lg border border-line bg-bg p-4 shadow-panel max-lg:hidden"
        :aria-label="t('demo_overwrite.tutorial.summary')"
      >
        <p class="m-0 mb-2 text-xs font-semibold tracking-[0.04em] text-fg-subtle uppercase">
          {{ t('demo_overwrite.tutorial.summary') }}
        </p>
        <ul class="m-0 flex list-none flex-col gap-0.5 p-0">
          <li v-for="entry in summary" :key="entry.id">
            <a
              :href="`#demo-overwrite-${entry.id}`"
              class="block rounded-sm px-2 py-1.5 text-sm text-fg-muted no-underline hover:bg-surface hover:text-fg"
              @click.prevent="scrollTo(entry.id)"
              >{{ entry.title }}</a
            >
          </li>
        </ul>
      </nav>
    </div>
  </div>
</template>
