<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Commentaires de la page : tri, liste paginée, modération et formulaire
 */
import { ref, useId } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useComments } from '@/vue/Composables/Front/useComments';
import CommentItem from '@/vue/Components/Front/StormFront/Comment/CommentItem.vue';
import CommentForm from '@/vue/Components/Front/StormFront/Comment/CommentForm.vue';
import PaginationNav from '@/vue/Components/Front/StormFront/Page/PaginationNav.vue';
import type { Page } from '@/ts/Front/Api/Page.type';
import type { Comment, CommentStatus } from '@/ts/Front/Api/Comment.type';

const props = defineProps<{ page: Page }>();
const context = useFrontContext();
const t = context.translate.main.articleFooter.comment;
const { data: comments, page, order, load, add, moderate, canModerate } = useComments(props.page);
const sortId = useId();
const message = ref<string | null>(null);

const SUCCESS: Record<CommentStatus, string> = {
  validate: t.successValidate,
  moderate: t.successModerate,
  wait_validation: t.successWaiting,
};

async function changeStatus(comment: Comment, status: CommentStatus, moderationComment: string | null): Promise<void> {
  try {
    await moderate(comment, status, moderationComment);
    message.value = SUCCESS[status];
  } catch (error) {
    message.value = context.errorLabel(error);
  }
}
</script>

<template>
  <section class="mt-18 max-w-170 border-t border-line pt-10" aria-labelledby="comments-title">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
      <h2 id="comments-title" class="text-[clamp(1.7rem,2.6vw,2.15rem)]">
        {{ t.title }}<template v-if="comments"> ({{ comments.total }})</template>
      </h2>
      <div>
        <label :for="sortId" class="sr-only">{{ t.sortLabel }}</label>
        <select :id="sortId" v-model="order" @change="load(1)">
          <option value="desc">{{ t.sortDesc }}</option>
          <option value="asc">{{ t.sortAsc }}</option>
        </select>
      </div>
    </div>
    <p v-if="message" role="status" class="rounded-sf border-l-3 border-primary bg-thread px-4 py-3">{{ message }}</p>
    <template v-if="comments">
      <CommentItem
        v-for="comment in comments.members"
        :key="comment.id"
        :comment="comment"
        :can-moderate="canModerate"
        @moderate="(status, moderationComment) => changeStatus(comment, status, moderationComment)"
      />
      <PaginationNav :page="page" :last-page="comments.lastPage" :translate="t.pagination" @go="load" />
    </template>
    <CommentForm :send="add" />
  </section>
</template>
