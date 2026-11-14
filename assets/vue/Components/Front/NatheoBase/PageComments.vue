<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Commentaires de la page dans le thème Natheo-Base : liste, envoi et modération
 */
import { ref } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useComments } from '@/vue/Composables/Front/useComments';
import { useCommentForm } from '@/vue/Composables/Front/useCommentForm';
import { useFormat } from '@/vue/Composables/Front/useFormat';
import PaginationNav from '@/vue/Components/Front/NatheoBase/PaginationNav.vue';
import type { Page } from '@/ts/Front/Api/Page.type';
import type { Comment, CommentStatus } from '@/ts/Front/Api/Comment.type';

const props = defineProps<{ page: Page }>();
const context = useFrontContext();
const t = context.translate.main.articleFooter.comment;
const { data: comments, page, load, add, moderate, canModerate } = useComments(props.page);

const { form, message, sending, submit } = useCommentForm(add);
const moderationError = ref<string | null>(null);
const format = useFormat();

async function changeStatus(comment: Comment, status: CommentStatus): Promise<void> {
  moderationError.value = null;
  try {
    await moderate(comment, status);
  } catch (error) {
    moderationError.value = context.errorLabel(error);
  }
}
</script>

<template>
  <section class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-6">
    <h2 class="text-2xl font-semibold">
      {{ t.title }} <span v-if="comments" class="text-gray-500">({{ comments.total }})</span>
    </h2>

    <p v-if="moderationError" role="alert">{{ moderationError }}</p>
    <ul v-if="comments" class="space-y-4">
      <li v-for="comment in comments.members" :key="comment.id" class="border border-gray-200 dark:border-gray-700 p-3">
        <p class="text-sm text-gray-500">
          {{ comment.author }}
          <span v-if="comment.createdAt"> · {{ format.dateTime(comment.createdAt) }}</span>
        </p>
        <p v-if="comment.comment" class="whitespace-pre-line">{{ comment.comment }}</p>
        <p v-if="comment.moderationComment" class="text-sm italic">
          {{ t.moderationPrefix }} {{ comment.moderationComment }}
        </p>
        <div v-if="canModerate" class="flex gap-3 mt-2 text-sm">
          <button
            v-if="comment.status !== 'validate'"
            type="button"
            class="underline"
            @click="changeStatus(comment, 'validate')"
          >
            {{ t.validate }}
          </button>
          <button
            v-if="comment.status !== 'moderate'"
            type="button"
            class="underline"
            @click="changeStatus(comment, 'moderate')"
          >
            {{ t.moderate }}
          </button>
          <button
            v-if="comment.status !== 'wait_validation'"
            type="button"
            class="underline"
            @click="changeStatus(comment, 'wait_validation')"
          >
            {{ t.waiting }}
          </button>
        </div>
      </li>
    </ul>
    <PaginationNav v-if="comments" :page="page" :last-page="comments.lastPage" :translate="t.pagination" @go="load" />

    <form class="space-y-3" @submit.prevent="submit">
      <p v-if="message" role="status">{{ message }}</p>
      <label class="block">
        <span class="block text-sm">{{ t.formNewCommentPseudoLabel }}</span>
        <input
          v-model="form.author"
          required
          maxlength="255"
          :placeholder="t.formNewCommentPseudoPlaceholder"
          class="w-full border p-2"
        />
      </label>
      <label class="block">
        <span class="block text-sm">{{ t.formNewCommentEmailLabel }}</span>
        <input
          v-model="form.email"
          type="email"
          maxlength="255"
          :placeholder="t.formNewCommentEmailPlaceholder"
          class="w-full border p-2"
        />
      </label>
      <label class="block">
        <span class="block text-sm">{{ t.formNewCommentCommentLabel }}</span>
        <textarea
          v-model="form.comment"
          required
          maxlength="10000"
          rows="4"
          :placeholder="t.formNewCommentCommentPlaceholder"
          class="w-full border p-2"
        ></textarea>
      </label>
      <button type="submit" :disabled="sending" class="px-4 py-2 border">{{ t.btnNewCommentSubmit }}</button>
    </form>
  </section>
</template>
