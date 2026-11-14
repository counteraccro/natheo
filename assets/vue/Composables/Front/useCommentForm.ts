/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Formulaire d'envoi d'un commentaire : saisie, envoi, état d'envoi et message de retour
 */
import { reactive, ref, type Ref } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import type { NewComment } from '@/ts/Front/Api/Comment.type';

export interface CommentForm {
  form: { author: string; email: string; comment: string };
  message: Ref<string | null>;
  sending: Ref<boolean>;
  submit(): Promise<void>;
}

/**
 * @param send envoi du commentaire, en général add() de useComments()
 */
export function useCommentForm(
  send: (comment: Omit<NewComment, 'page' | 'userAgent'>) => Promise<unknown>
): CommentForm {
  const context = useFrontContext();
  const form = reactive({ author: context.datas.user?.login ?? '', email: '', comment: '' });
  const message = ref<string | null>(null);
  const sending = ref(false);

  async function submit(): Promise<void> {
    sending.value = true;
    message.value = null;
    try {
      await send({ author: form.author, email: form.email || null, comment: form.comment });
      form.comment = '';
      message.value = context.translate.main.articleFooter.comment.formNewCommentSuccessMessage;
    } catch (error) {
      message.value = context.errorLabel(error);
    } finally {
      sending.value = false;
    }
  }

  return { form, message, sending, submit };
}
