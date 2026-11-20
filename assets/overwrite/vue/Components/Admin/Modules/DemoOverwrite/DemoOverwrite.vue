<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Écran de l'exemple de surcharge : réponse de GET /api/v2/info, dont le provider est surchargé, et fichiers qui la
 * produisent
 */
import { computed, onMounted, ref } from 'vue';
import { AdminPageHeader, UiAlert, UiBadge, UiSkeleton } from '@ui';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import type { Info } from '@/ts/Global/Api/Info.type';

const { api, menu, t } = useAdminContext();

const info = ref<Info | null>(null);
const failed = ref(false);
const icon = computed(() => menu.items.value.find((item) => item.id === 'demo_overwrite')?.icon);
const extra = computed(() => (info.value?.extra ? JSON.stringify(info.value.extra, null, 2) : null));

const files = computed(() => [
  {
    paths: ['config/cms/overwrite.yaml', 'src/Overwrite/Api/State/Provider/System/InfoProvider.php'],
    text: t('demo_overwrite.screen.file_api'),
  },
  {
    paths: ['assets/overwrite/vue/Components/Admin/Modules/DemoOverwrite/'],
    text: t('demo_overwrite.screen.file_screen'),
  },
  { paths: ['src/Overwrite/Module/DemoOverwriteModule.php'], text: t('demo_overwrite.screen.file_module') },
]);

onMounted(async () => {
  try {
    info.value = await api.get<Info>('/info');
  } catch {
    failed.value = true;
  }
});

const PANEL = 'rounded-lg border border-line bg-bg shadow-panel';
const PANEL_HEAD = 'flex flex-wrap items-center gap-3 border-b border-line px-5 py-4';
const PANEL_TITLE = 'm-0 text-md font-[650]';
</script>

<template>
  <div class="flex flex-col gap-6">
    <AdminPageHeader
      :title="t('demo_overwrite.screen.title')"
      :description="t('demo_overwrite.screen.description')"
      :icon-path="icon"
    />

    <section :class="PANEL">
      <header :class="PANEL_HEAD">
        <h2 :class="PANEL_TITLE">{{ t('demo_overwrite.screen.response_title') }}</h2>
        <UiBadge v-if="info" :tone="info.extra ? 'success' : 'neutral'">
          {{ info.extra ? t('demo_overwrite.screen.status_active') : t('demo_overwrite.screen.status_inactive') }}
        </UiBadge>
      </header>
      <div class="flex flex-col gap-5 p-5">
        <UiAlert v-if="failed" variant="danger">{{ t('demo_overwrite.screen.error') }}</UiAlert>
        <UiSkeleton v-else-if="!info" variant="text" :lines="4" />
        <template v-else>
          <dl class="m-0 grid grid-cols-[max-content_minmax(0,1fr)] gap-x-8 gap-y-2.5">
            <dt class="text-fg-muted">{{ t('demo_overwrite.screen.field_site_name') }}</dt>
            <dd class="m-0 font-medium">{{ info.siteName }}</dd>
            <dt class="text-fg-muted">{{ t('demo_overwrite.screen.field_natheo_version') }}</dt>
            <dd class="m-0 font-medium tabular-nums">{{ info.natheoVersion }}</dd>
            <dt class="text-fg-muted">{{ t('demo_overwrite.screen.field_api_version') }}</dt>
            <dd class="m-0 font-medium tabular-nums">{{ info.apiVersion }}</dd>
          </dl>
          <div>
            <h3 class="m-0 mb-2 text-sm font-[650]">{{ t('demo_overwrite.screen.field_extra') }}</h3>
            <pre
              v-if="extra"
              class="m-0 overflow-x-auto rounded-md bg-code px-4 py-3 font-mono text-sm leading-relaxed text-on-code"
            ><code>{{ extra }}</code></pre>
            <UiAlert v-else variant="info">{{ t('demo_overwrite.screen.inactive_text') }}</UiAlert>
          </div>
        </template>
      </div>
    </section>

    <section :class="PANEL">
      <header :class="PANEL_HEAD">
        <h2 :class="PANEL_TITLE">{{ t('demo_overwrite.screen.files_title') }}</h2>
      </header>
      <ul class="m-0 list-none p-0">
        <li
          v-for="file in files"
          :key="file.paths[0]"
          class="flex flex-col gap-1.5 border-b border-line px-5 py-4 last:border-b-0"
        >
          <span class="flex flex-wrap gap-2">
            <code
              v-for="path in file.paths"
              :key="path"
              class="rounded-sm bg-surface px-1.5 py-0.5 font-mono text-[13px] text-fg"
              >{{ path }}</code
            >
          </span>
          <span class="text-fg-muted">{{ file.text }}</span>
        </li>
      </ul>
    </section>
  </div>
</template>
