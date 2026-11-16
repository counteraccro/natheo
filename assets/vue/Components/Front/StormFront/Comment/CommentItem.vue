<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Un commentaire, filet à gauche : auteur, date relative, statut, message et actions de modération
 */
import { computed, ref, useId } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useFormat } from '@/vue/Composables/Front/useFormat';
import type { Comment, CommentStatus } from '@/ts/Front/Api/Comment.type';

const props = defineProps<{ comment: Comment; canModerate: boolean }>();
const emit = defineEmits<{ moderate: [status: CommentStatus, moderationComment: string | null] }>();
const { translate } = useFrontContext();
const format = useFormat();
const t = translate.main.articleFooter.comment;
const fieldId = useId();
const moderating = ref(false);
const moderationComment = ref(props.comment.moderationComment ?? '');

const relativeDate = computed(() => format.relative(props.comment.createdAt));

const status = computed(() => {
  if (props.comment.status === 'wait_validation') {
    return { label: t.statusWait, classes: 'bg-notice text-on-notice' };
  }
  return props.comment.status === 'moderate' ? { label: t.statusModerate, classes: 'bg-thread text-fg-muted' } : null;
});

function submitModeration(): void {
  emit('moderate', 'moderate', moderationComment.value || null);
  moderating.value = false;
}

const ACTION =
  'inline-flex min-h-9 cursor-pointer items-center rounded-sf px-3 text-[0.88rem] font-semibold text-fg shadow-[inset_0_0_0_1.5px_currentColor]';
</script>

<template>
  <article class="mt-4 border-l-2 border-line py-5 pl-5">
    <header class="mb-1.5 flex flex-wrap items-baseline gap-x-4 gap-y-1">
      <strong v-if="comment.author" class="font-semibold">{{ comment.author }}</strong>
      <time v-if="comment.createdAt" :datetime="comment.createdAt" class="font-mono text-[0.82rem] text-fg-muted">
        {{ relativeDate }}
      </time>
      <span v-if="status" class="rounded-sf px-2 py-0.5 text-[0.82rem] font-semibold" :class="status.classes">
        {{ status.label }}
      </span>
    </header>
    <p v-if="comment.comment" class="text-[1.05rem] leading-relaxed whitespace-pre-line">{{ comment.comment }}</p>
    <p v-if="comment.moderationComment" class="mt-1 text-fg-muted italic">
      {{ t.moderationPrefix }} {{ comment.moderationComment }}
    </p>

    <div v-if="canModerate && !moderating" class="mt-3 flex flex-wrap gap-2">
      <button v-if="comment.status !== 'validate'" type="button" :class="ACTION" @click="emit('moderate', 'validate', null)">
        {{ t.validate }}
      </button>
      <button v-if="comment.status !== 'moderate'" type="button" :class="ACTION" @click="moderating = true">
        {{ t.moderate }}
      </button>
      <button
        v-if="comment.status !== 'wait_validation'"
        type="button"
        :class="ACTION"
        @click="emit('moderate', 'wait_validation', null)"
      >
        {{ t.waiting }}
      </button>
    </div>
    <form v-else-if="canModerate" class="mt-3 grid gap-2" @submit.prevent="submitModeration">
      <label :for="fieldId">{{ t.formModerateLabel }}</label>
      <textarea :id="fieldId" v-model="moderationComment" :placeholder="t.formModeratePlaceHolder" maxlength="10000" />
      <div class="flex justify-end gap-2">
        <button type="button" :class="ACTION" @click="moderating = false">{{ t.formModerateCancel }}</button>
        <button
          type="submit"
          class="inline-flex min-h-9 cursor-pointer items-center rounded-sf bg-primary px-3 text-[0.88rem] font-semibold text-on-primary"
        >
          {{ t.formModerateSubmit }}
        </button>
      </div>
    </form>
  </article>
</template>
