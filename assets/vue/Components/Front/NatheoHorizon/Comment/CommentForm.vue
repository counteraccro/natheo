<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Formulaire d'envoi d'un commentaire
 */
import { useId } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useCommentForm } from '@/vue/Composables/Front/useCommentForm';
import type { NewComment } from '@/ts/Front/Api/Comment.type';

const props = defineProps<{ send: (comment: Omit<NewComment, 'page' | 'userAgent'>) => Promise<unknown> }>();
const context = useFrontContext();
const t = context.translate.main.articleFooter.comment;
const id = useId();
const { form, message, sending, submit } = useCommentForm(props.send);
</script>

<template>
  <form class="mt-8 grid gap-4 rounded-nh-lg bg-surface p-7" @submit.prevent="submit">
    <h3 class="text-[1.3rem]">{{ t.btnNewComment }}</h3>
    <p v-if="message" role="status" class="rounded-nh border-l-4 border-mod-third bg-bg px-4 py-3">{{ message }}</p>
    <div class="grid gap-4 sm:grid-cols-2">
      <div>
        <label :for="`${id}-author`">{{ t.formNewCommentPseudoLabel }}</label>
        <input :id="`${id}-author`" v-model="form.author" required maxlength="255" autocomplete="nickname" />
      </div>
      <div>
        <label :for="`${id}-email`">{{ t.formNewCommentEmailLabel }}</label>
        <input :id="`${id}-email`" v-model="form.email" type="email" maxlength="255" autocomplete="email" />
      </div>
    </div>
    <div>
      <label :for="`${id}-comment`">{{ t.formNewCommentCommentLabel }}</label>
      <textarea :id="`${id}-comment`" v-model="form.comment" required maxlength="10000" />
    </div>
    <div class="flex justify-end">
      <button
        type="submit"
        :disabled="sending"
        class="inline-flex min-h-11 cursor-pointer items-center rounded-nh bg-primary px-4 font-semibold text-on-primary hover:brightness-110 disabled:opacity-60"
      >
        {{ t.btnNewCommentSubmit }}
      </button>
    </div>
  </form>
</template>
