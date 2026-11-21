<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Aperçu du tag en cours d'édition au rendu du thème actif du site : une iframe par langue, mode clair ou sombre,
 * tag renvoyé à chaque modification
 */
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { UiSwitch, UiTabs } from '@ui';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { localeName } from '@/vue/Composables/Admin/Screen/screenFormat';
import { postPreviewMessage, readPreviewMessage } from '@/vue/Composables/Global/previewMessage';
import type { FormScreenState } from '@/ts/Admin/Screen/FormScreen.type';
import type { AdminTag } from '@/ts/Admin/Api/AdminTag.type';

/** Hauteur du cadre avant la première mesure envoyée par l'aperçu */
const MIN_HEIGHT = 64;

const props = defineProps<{ data: Partial<AdminTag>; screen: FormScreenState<AdminTag> }>();

const { t, bootstrap } = useAdminContext();
const locale = ref(props.screen.currentLocale);
const dark = ref(false);
const height = ref(MIN_HEIGHT);
const ready = ref(false);
// Par langue : en changeant d'onglet, le cadre retiré ne doit pas effacer celui qui vient d'apparaître
const frames = new Map<string, HTMLIFrameElement>();
const frame = (): HTMLIFrameElement | undefined => frames.get(locale.value);
const tabs = props.screen.locales.map((code) => ({ id: code, label: localeName(code, bootstrap.locale) }));
const src = computed(() => `${bootstrap.basePath}tag/preview/${locale.value}`);

const tag = computed(() => ({
  id: props.data.id ?? 0,
  label: props.data.translations?.find((translation) => translation.locale === locale.value)?.label ?? '',
  color: props.data.color ?? '#000000',
}));

const setFrame = (code: string, element: unknown): void => {
  if (element instanceof HTMLIFrameElement) frames.set(code, element);
  else frames.delete(code);
};

const send = (): void => {
  if (!ready.value) return;
  postPreviewMessage(frame()?.contentWindow, { type: 'data', data: { ...tag.value } });
  postPreviewMessage(frame()?.contentWindow, { type: 'scheme', dark: dark.value });
};

const onMessage = (event: MessageEvent): void => {
  const message = readPreviewMessage(event, frame()?.contentWindow);
  if (message?.type === 'ready') {
    ready.value = true;
    send();
  } else if (message?.type === 'height') {
    height.value = Math.max(MIN_HEIGHT, message.height);
  } else if (message?.type === 'scheme') {
    dark.value = message.dark;
  }
};

watch([tag, dark], send, { deep: true });
watch(locale, () => {
  ready.value = false;
  height.value = MIN_HEIGHT;
});
onMounted(() => window.addEventListener('message', onMessage));
onBeforeUnmount(() => window.removeEventListener('message', onMessage));
</script>

<template>
  <div class="flex flex-col gap-2">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <p class="m-0 font-semibold text-fg">{{ t('tag.editor.preview') }}</p>
        <p class="m-0 text-sm text-fg-subtle">{{ t('tag.editor.preview_help') }}</p>
      </div>
      <div class="w-40">
        <UiSwitch v-model="dark" :label="t('tag.editor.preview_dark')" />
      </div>
    </div>
    <UiTabs v-model="locale" :tabs="tabs" :label="t('tag.editor.preview')">
      <template v-for="tab in tabs" :key="tab.id" #[tab.id]>
        <iframe
          v-if="tab.id === locale"
          :ref="(element) => setFrame(tab.id, element)"
          :src="src"
          :title="t('tag.editor.preview')"
          class="block w-full rounded-md border border-line"
          :style="{ height: height + 'px' }"
        ></iframe>
      </template>
    </UiTabs>
  </div>
</template>
