<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Bandeau au-dessus de la liste des commentaires : nombre par statut (filtre au clic), rappel des options du site
 * et filtre par page retirable
 */
import { computed, onMounted, onScopeDispose, ref } from 'vue';
import { UiButton, UiIcon } from '@ui';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { onResourceChange } from '@/vue/Composables/Admin/Screen/resourceChanges';
import { COMMENT_STATUSES } from '@/vue/Composables/Admin/Comment/commentStatus';
import type { CollectionScreenState } from '@/ts/Admin/Screen/CollectionScreen.type';
import type { AdminComment, AdminCommentStatusCount } from '@/ts/Admin/Api/AdminComment.type';

/** Ressource des nombres par statut */
const COUNTS = '/admin/comment_status_counts';

/** Couleur de l'icône d'un statut */
const TONE_TEXT: Record<string, string> = { warning: 'text-warning', success: 'text-success', danger: 'text-danger' };

const props = defineProps<{ screen: CollectionScreenState<AdminComment> }>();

const { t, api, bootstrap, menu } = useAdminContext();
const counts = ref<Record<string, number>>({});
const moderation = props.screen.filter.moderation;
const pageId = props.screen.filter.pageId;
const optionsUrl = computed(() => menu.items.value.find((item) => item.id === 'option_system')?.legacyUrl ?? null);
// Titre lu dans les commentaires affichés, tous de cette page
const pageTitle = computed(() => {
  const page = props.screen.collection.items.value.find((item) => String(item.page?.id) === pageId.value)?.page;
  return page?.title ?? t('comment.banner.page_unknown', { id: pageId.value });
});

const load = async (): Promise<void> => {
  try {
    const result = await api.getCollection<AdminCommentStatusCount>(COUNTS);
    counts.value = Object.fromEntries(result.members.map((member) => [member.status, member.count]));
  } catch {
    counts.value = {};
  }
};

onMounted(() => void load());
onScopeDispose(onResourceChange(props.screen.descriptor.resource, () => void load()));
</script>

<template>
  <div class="@container mb-4">
    <div class="grid grid-cols-3 gap-3 @3xl:grid-cols-[repeat(3,minmax(0,1fr))_minmax(0,1.4fr)]">
      <button
        v-for="status in COMMENT_STATUSES"
        :key="status.value"
        type="button"
        class="ui-ring grid cursor-pointer gap-0.5 rounded-lg border border-line bg-bg px-3.5 py-3 text-left shadow-panel hover:border-line-strong aria-pressed:border-primary aria-pressed:shadow-[0_0_0_1px_var(--primary)]"
        :aria-pressed="moderation === status.value"
        @click="moderation = moderation === status.value ? '' : status.value"
      >
        <span class="flex items-center gap-1.5 text-sm font-semibold text-fg-muted">
          <UiIcon :name="status.icon" class="size-4 shrink-0" :class="TONE_TEXT[status.tone]" />
          <span class="truncate">{{ t(status.label) }}</span>
        </span>
        <b class="text-[22px] font-bold tracking-[-0.01em] tabular-nums">{{ counts[status.value] ?? '–' }}</b>
      </button>
      <div
        class="col-span-3 grid content-center gap-1 rounded-lg bg-surface px-3.5 py-3 text-sm text-fg-muted @3xl:col-span-1"
      >
        <p class="m-0 flex items-center gap-2">
          <UiIcon name="message" class="size-4 shrink-0" />
          {{ bootstrap.options.openComment ? t('comment.banner.open') : t('comment.banner.closed') }}
        </p>
        <p v-if="bootstrap.options.openComment" class="m-0 flex items-center gap-2">
          <UiIcon name="hourglass" class="size-4 shrink-0" />
          {{ bootstrap.options.commentWaitValidation ? t('comment.banner.wait_on') : t('comment.banner.wait_off') }}
        </p>
        <a v-if="optionsUrl" :href="optionsUrl" class="ui-ring w-fit rounded-sm font-semibold text-primary">
          {{ t('comment.banner.options_link') }}
        </a>
      </div>
    </div>
    <p
      v-if="pageId"
      class="m-0 mt-3 inline-flex items-center gap-1 rounded-full py-0.5 bg-primary-soft pr-1 pl-2.5 text-sm font-semibold text-on-primary-soft"
    >
      {{ t('comment.banner.page_filter', { title: pageTitle }) }}
      <UiButton
        variant="ghost"
        size="sm"
        icon="close"
        :aria-label="t('comment.banner.page_filter_remove')"
        @click="pageId = ''"
      />
    </p>
  </div>
</template>
