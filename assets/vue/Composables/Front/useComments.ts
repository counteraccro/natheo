/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Commentaires d'une page : liste paginée et triée, envoi, modération
 */
import { computed, ref, type ComputedRef, type Ref } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useAsync, type AsyncState } from '@/vue/Composables/Front/useAsync';
import type { Paginated } from '@/ts/Front/Api/Hydra.type';
import type { Page } from '@/ts/Front/Api/Page.type';
import type { Comment, CommentStatus, ModerateComment, NewComment } from '@/ts/Front/Api/Comment.type';

export type CommentOrder = 'asc' | 'desc';

export interface Comments extends AsyncState<Paginated<Comment>> {
  page: Ref<number>;
  order: Ref<CommentOrder>;
  canModerate: ComputedRef<boolean>;
  load(page?: number): Promise<void>;
  /** Envoie un commentaire et recharge la liste ; renvoie le commentaire créé (statut selon la modération) */
  add(comment: Omit<NewComment, 'page' | 'userAgent'>): Promise<Comment>;
  moderate(comment: Comment, status: CommentStatus, moderationComment?: string | null): Promise<Comment>;
}

export function useComments(page: Page, itemsPerPage = 20): Comments {
  const context = useFrontContext();
  const state = useAsync<Paginated<Comment>>();
  const current = ref(1);
  const order = ref<CommentOrder>('desc');
  const canModerate = computed(() => context.datas.user?.canModerate ?? false);

  async function load(target = current.value): Promise<void> {
    current.value = target;
    await state.run(() =>
      context.api.getCollection<Comment>(`/pages/${page.id}/comments`, {
        page: target,
        itemsPerPage,
        order: order.value,
      })
    );
  }

  async function add(comment: Omit<NewComment, 'page' | 'userAgent'>): Promise<Comment> {
    const created = await context.api.post<Comment>('/comments', {
      ...comment,
      page: page['@id'],
      userAgent: navigator.userAgent,
    } satisfies NewComment);
    await load(1);
    return created;
  }

  async function moderate(comment: Comment, status: CommentStatus, moderationComment: string | null = null) {
    const body: ModerateComment = { status, moderationComment };
    const updated = await context.api.patch<Comment>(comment['@id'], body);
    await load();
    return updated;
  }

  load(1);
  return { ...state, page: current, order, canModerate, load, add, moderate };
}
