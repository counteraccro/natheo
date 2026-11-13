<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Un commentaire : auteur, date relative, statut, message de modération et actions de modération
 */
import { computed, ref, useId } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import type { Comment, CommentStatus } from '@/ts/Front/Api/Comment.type';

const props = defineProps<{ comment: Comment; canModerate: boolean }>();
const emit = defineEmits<{ moderate: [status: CommentStatus, moderationComment: string | null] }>();
const { datas, translate } = useFrontContext();
const t = translate.main.articleFooter.comment;
const fieldId = useId();
const moderating = ref(false);
const moderationComment = ref(props.comment.moderationComment ?? '');

const UNITS: [Intl.RelativeTimeFormatUnit, number][] = [
  ['year', 31536000],
  ['month', 2592000],
  ['week', 604800],
  ['day', 86400],
  ['hour', 3600],
  ['minute', 60],
];

const relativeDate = computed(() => {
  if (!props.comment.createdAt) {
    return null;
  }
  const seconds = (new Date(props.comment.createdAt).getTime() - Date.now()) / 1000;
  const format = new Intl.RelativeTimeFormat(datas.locale, { numeric: 'auto' });
  const [unit, size] = UNITS.find(([, size]) => Math.abs(seconds) >= size) ?? ['minute', 60];
  return format.format(Math.round(seconds / size), unit);
});

const status = computed(() => {
  if (props.comment.status === 'wait_validation') {
    return { label: t.statusWait, classes: 'bg-notice text-on-notice' };
  }
  return props.comment.status === 'moderate' ? { label: t.statusModerate, classes: 'bg-surface text-fg-muted' } : null;
});

function submitModeration(): void {
  emit('moderate', 'moderate', moderationComment.value || null);
  moderating.value = false;
}

const action = 'inline-flex min-h-9 cursor-pointer items-center rounded-nh px-3 text-[0.88rem] font-semibold text-fg shadow-[inset_0_0_0_1.5px_currentColor]';
</script>

<template>
  <article class="grid grid-cols-[44px_1fr] gap-4 border-b border-line py-5">
    <span class="grid size-11 place-items-center rounded-nh bg-surface font-bold text-primary" aria-hidden="true">
      {{ (comment.author ?? '?').slice(0, 1).toUpperCase() }}
    </span>
    <div class="min-w-0">
      <header class="mb-1 flex flex-wrap items-baseline gap-x-3.5 gap-y-1">
        <strong v-if="comment.author">{{ comment.author }}</strong>
        <time v-if="comment.createdAt" :datetime="comment.createdAt" class="text-[0.9rem] text-fg-muted">{{ relativeDate }}</time>
        <span v-if="status" class="rounded-nh px-2 py-0.5 text-[0.85rem] font-semibold" :class="status.classes">{{ status.label }}</span>
      </header>
      <p v-if="comment.comment" class="whitespace-pre-line">{{ comment.comment }}</p>
      <p v-if="comment.moderationComment" class="mt-1 text-fg-muted italic">
        {{ t.moderationPrefix }} {{ comment.moderationComment }}
      </p>

      <div v-if="canModerate && !moderating" class="mt-3 flex flex-wrap gap-2">
        <button v-if="comment.status !== 'validate'" type="button" :class="action" @click="emit('moderate', 'validate', null)">
          {{ t.validate }}
        </button>
        <button v-if="comment.status !== 'moderate'" type="button" :class="action" @click="moderating = true">
          {{ t.moderate }}
        </button>
        <button
          v-if="comment.status !== 'wait_validation'"
          type="button"
          :class="action"
          @click="emit('moderate', 'wait_validation', null)"
        >
          {{ t.waiting }}
        </button>
      </div>
      <form v-else-if="canModerate" class="mt-3 grid gap-2" @submit.prevent="submitModeration">
        <label :for="fieldId">{{ t.formModerateLabel }}</label>
        <textarea :id="fieldId" v-model="moderationComment" :placeholder="t.formModeratePlaceHolder" maxlength="10000" />
        <div class="flex justify-end gap-2">
          <button type="button" :class="action" @click="moderating = false">{{ t.formModerateCancel }}</button>
          <button
            type="submit"
            class="inline-flex min-h-9 cursor-pointer items-center rounded-nh bg-primary px-3 text-[0.88rem] font-semibold text-on-primary"
          >
            {{ t.formModerateSubmit }}
          </button>
        </div>
      </form>
    </div>
  </article>
</template>
