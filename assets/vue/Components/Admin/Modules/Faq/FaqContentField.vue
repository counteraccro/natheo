<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Arbre des catégories et des questions d'une FAQ dans son formulaire : déplacements (poignée ou menu de la ligne),
 * activations et suppressions enregistrés aussitôt, catégorie ou question modifiée dans sa ligne ; lecture seule sans
 * poignée ni action
 */
import { computed, nextTick, onBeforeUnmount, reactive, watch, type ComponentPublicInstance } from 'vue';
import Sortable from 'sortablejs';
import { UiBadge, UiButton, UiDropdown, UiDropdownItem, UiDropdownLabel, UiDropdownSeparator, UiIcon } from '@ui';
import FaqCategoryEditor from '@admin/Modules/Faq/FaqCategoryEditor.vue';
import FaqQuestionEditor from '@admin/Modules/Faq/FaqQuestionEditor.vue';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { useFaqTree } from '@/vue/Composables/Admin/Faq/useFaqTree';
import type { FormScreenState } from '@/ts/Admin/Screen/FormScreen.type';
import type { AdminFaq, AdminFaqTreeCategory, AdminFaqTreeQuestion } from '@/ts/Admin/Api/AdminFaq.type';

const props = defineProps<{ data: Partial<AdminFaq>; screen: FormScreenState<AdminFaq> }>();

const { t, bootstrap } = useAdminContext();
const tree = useFaqTree(Number(props.data.id), props.data.categories ?? [], props.screen.resource);
const { categories, line, busy, savedAt, questionCount } = tree;
const closed = reactive(new Set<number>());

// La FAQ relue par le formulaire apporte l'arbre à jour
watch(
  () => props.data.categories,
  (list) => tree.setCategories(list ?? [])
);

const readOnly = computed(() => !props.screen.canEdit.value);
const permissions = computed(() => props.data.permissions ?? { edit: false, disable: false, delete: false });
const savedText = computed(() =>
  savedAt.value
    ? t('faq.content.saved_at', {
        time: savedAt.value.toLocaleTimeString(bootstrap.locale, { hour: '2-digit', minute: '2-digit' }),
      })
    : t('faq.content.saved')
);

const isLine = (type: 'category' | 'question', id: number | null): boolean =>
  line.value?.type === type && line.value.id === id;
const newQuestionIn = (category: AdminFaqTreeCategory): boolean =>
  line.value?.type === 'question' && line.value.id === null && line.value.categoryId === category.id;
const questionLine = computed(() => (line.value?.type === 'question' ? line.value : null));
const categoryLine = computed(() => (line.value?.type === 'category' ? line.value : null));

const toggleOpen = (category: AdminFaqTreeCategory): void => {
  if (closed.has(category.id)) closed.delete(category.id);
  else closed.add(category.id);
};

const addQuestion = async (category: AdminFaqTreeCategory): Promise<void> => {
  closed.delete(category.id);
  await tree.openQuestion(category);
};

const otherCategories = (category: AdminFaqTreeCategory) => categories.value.filter((c) => c.id !== category.id);

/** Poignées de glisser-déposer : une liste des catégories, une liste des questions par catégorie */
const sortables = new Map<string, Sortable>();

/**
 * Remet la ligne déplacée à sa place : l'arbre relu après l'écriture la replace là où le serveur l'a mise
 * @param event
 */
const revert = (event: Sortable.SortableEvent): void => {
  const { item, from, oldIndex } = event;
  item.remove();
  from.insertBefore(item, from.children[oldIndex ?? 0] ?? null);
};

const bindList = (key: string, element: Element | ComponentPublicInstance | null, options: Sortable.Options): void => {
  const list = element instanceof HTMLElement ? element : null;
  const existing = sortables.get(key);
  if (existing && existing.el === list) return;
  existing?.destroy();
  sortables.delete(key);
  if (list && !readOnly.value) {
    sortables.set(
      key,
      Sortable.create(list, { handle: '[data-faq-handle]', draggable: '[data-faq-item]', animation: 150, ...options })
    );
  }
};

const bindCategories = (element: Element | ComponentPublicInstance | null): void =>
  bindList('categories', element, {
    group: 'faq-categories',
    onEnd: (event) => {
      revert(event);
      const category = categories.value[event.oldDraggableIndex ?? -1];
      if (category && event.newDraggableIndex !== undefined)
        void tree.moveCategory(category, event.newDraggableIndex + 1);
    },
  });

const bindQuestions = (category: AdminFaqTreeCategory, element: Element | ComponentPublicInstance | null): void =>
  bindList(`questions-${category.id}`, element, {
    group: 'faq-questions',
    onEnd: (event) => {
      revert(event);
      const question = category.questions[event.oldDraggableIndex ?? -1];
      const target = categories.value.find((c) => String(c.id) === (event.to as HTMLElement).dataset.faqCategory);
      if (question && target && event.newDraggableIndex !== undefined) {
        void tree.moveQuestion(question, target, event.newDraggableIndex + 1);
      }
    },
  });

watch(readOnly, () => {
  sortables.forEach((sortable) => sortable.option('disabled', readOnly.value));
});
onBeforeUnmount(() => sortables.forEach((sortable) => sortable.destroy()));

const moveQuestionTo = async (question: AdminFaqTreeQuestion, target: AdminFaqTreeCategory): Promise<void> => {
  closed.delete(target.id);
  await tree.moveQuestion(question, target, target.questions.length + 1);
};

const focusLine = async (): Promise<void> => {
  await nextTick();
  document.querySelector<HTMLElement>('[data-faq-line] input:not([disabled])')?.focus();
};

const openCategory = async (category?: AdminFaqTreeCategory): Promise<void> => {
  if (await tree.openCategory(category)) await focusLine();
};
const openQuestion = async (category: AdminFaqTreeCategory, question?: AdminFaqTreeQuestion): Promise<void> => {
  if (await tree.openQuestion(category, question)) await focusLine();
};
</script>

<template>
  <div class="flex flex-col gap-3" :aria-busy="busy">
    <div class="flex flex-wrap items-center gap-2.5">
      <span class="text-sm font-[550] text-fg-muted">
        {{ t('faq.content.count', { categories: categories.length, questions: questionCount }) }}
      </span>
      <span v-if="!readOnly" class="flex items-center gap-1.5 text-xs text-fg-subtle" role="status">
        <UiIcon
          :name="busy ? 'refresh' : 'check-circle'"
          class="size-4"
          :class="busy ? 'animate-spin text-fg-subtle' : 'text-success'"
        />
        {{ busy ? t('faq.content.saving') : savedText }}
      </span>
      <UiButton v-if="!readOnly" size="sm" icon="plus" class="ml-auto" @click="openCategory()">
        {{ t('faq.content.add_category') }}
      </UiButton>
    </div>
    <p v-if="!readOnly" class="m-0 flex gap-2 rounded-md bg-surface px-3 py-2.5 text-xs text-fg-muted">
      <UiIcon name="info" class="mt-px size-4 shrink-0 text-fg-subtle" />
      <span>{{ t('faq.content.note') }}</span>
    </p>

    <ol :ref="bindCategories" class="m-0 flex list-none flex-col gap-2.5 p-0" :aria-label="t('faq.content.categories')">
      <li
        v-for="(category, index) in categories"
        :key="category.id"
        data-faq-item
        class="rounded-lg border border-line"
        :class="[
          category.disabled ? 'bg-surface' : 'bg-bg',
          isLine('category', category.id) ? 'border-transparent' : '',
        ]"
      >
        <div v-if="categoryLine && isLine('category', category.id)" data-faq-line>
          <FaqCategoryEditor :key="category.id" :tree="tree" :line="categoryLine" />
        </div>
        <div v-else class="flex min-h-[46px] items-center gap-1.5 py-1.5 pr-1.5 pl-1">
          <span
            v-if="!readOnly"
            data-faq-handle
            class="grid h-[30px] w-[22px] shrink-0 cursor-grab place-items-center rounded-sm text-fg-subtle hover:bg-surface hover:text-fg"
            :title="t('faq.content.handle_category')"
            aria-hidden="true"
          >
            <UiIcon name="grip" class="size-4" />
          </span>
          <button
            type="button"
            class="flex h-[34px] min-w-0 flex-1 items-center gap-1.5 rounded-sm border-0 bg-transparent px-1.5 text-left font-[650] text-fg hover:bg-surface"
            :aria-expanded="!closed.has(category.id)"
            :aria-controls="`faq-category-${category.id}`"
            @click="toggleOpen(category)"
          >
            <UiIcon
              name="chevron-down"
              class="size-4 text-fg-subtle transition-transform"
              :class="closed.has(category.id) ? '-rotate-90' : ''"
            />
            <span class="truncate">{{ category.title }}</span>
          </button>
          <UiBadge v-if="category.disabled" tone="warning">{{ t('faq.content.badge_disabled') }}</UiBadge>
          <UiBadge :dot="false">{{ t('faq.content.question_count', { count: category.questions.length }) }}</UiBadge>
          <UiDropdown
            v-if="!readOnly"
            :label="t('faq.content.menu_category', { title: category.title })"
            icon="dots-vertical"
            icon-only
            variant="ghost"
            size="sm"
          >
            <UiDropdownItem icon="edit" @select="openCategory(category)">{{
              t('faq.content.action_edit')
            }}</UiDropdownItem>
            <UiDropdownItem icon="plus" @select="addQuestion(category)">
              {{ t('faq.content.action_add_question') }}
            </UiDropdownItem>
            <UiDropdownSeparator />
            <UiDropdownItem icon="arrow-up" :disabled="index === 0" @select="tree.moveCategory(category, index)">
              {{ t('faq.content.action_up') }}
            </UiDropdownItem>
            <UiDropdownItem
              icon="arrow-down"
              :disabled="index === categories.length - 1"
              @select="tree.moveCategory(category, index + 2)"
            >
              {{ t('faq.content.action_down') }}
            </UiDropdownItem>
            <template v-if="permissions.disable || permissions.delete">
              <UiDropdownSeparator />
              <UiDropdownItem
                v-if="permissions.disable"
                :icon="category.disabled ? 'check-circle' : 'ban'"
                @select="tree.toggleCategory(category)"
              >
                {{ category.disabled ? t('faq.content.action_enable') : t('faq.content.action_disable') }}
              </UiDropdownItem>
              <UiDropdownItem v-if="permissions.delete" icon="trash" danger @select="tree.deleteCategory(category)">
                {{ t('faq.content.action_delete') }}
              </UiDropdownItem>
            </template>
          </UiDropdown>
        </div>

        <div
          v-show="!closed.has(category.id) || newQuestionIn(category)"
          :id="`faq-category-${category.id}`"
          class="pr-2 pb-2"
          :class="readOnly ? 'pl-3.5' : 'pl-[30px]'"
        >
          <ol
            :ref="(element) => bindQuestions(category, element)"
            :data-faq-category="category.id"
            class="m-0 flex list-none flex-col border-t border-line p-0"
          >
            <li
              v-for="question in category.questions"
              :key="question.id"
              data-faq-item
              class="border-b border-line"
              :class="isLine('question', question.id) ? 'py-3.5' : 'flex min-h-[42px] items-center gap-1 py-0.5'"
            >
              <div v-if="questionLine && isLine('question', question.id)" data-faq-line>
                <FaqQuestionEditor
                  :key="question.id"
                  :tree="tree"
                  :line="questionLine"
                  :category="category"
                  :read-only="readOnly"
                />
              </div>
              <template v-else>
                <span
                  v-if="!readOnly"
                  data-faq-handle
                  class="grid h-[30px] w-[22px] shrink-0 cursor-grab place-items-center rounded-sm text-fg-subtle hover:bg-surface hover:text-fg"
                  :title="t('faq.content.handle_question')"
                  aria-hidden="true"
                >
                  <UiIcon name="grip" class="size-4" />
                </span>
                <button
                  type="button"
                  class="min-w-0 flex-1 rounded-sm border-0 bg-transparent px-2 py-1.5 text-left hover:bg-surface hover:text-primary"
                  :class="question.disabled ? 'text-fg-subtle' : 'text-fg'"
                  :aria-expanded="false"
                  @click="openQuestion(category, question)"
                >
                  {{ question.title }}
                </button>
                <UiBadge v-if="question.disabled" tone="warning">{{ t('faq.content.badge_disabled') }}</UiBadge>
                <UiDropdown
                  v-if="!readOnly"
                  :label="t('faq.content.menu_question', { title: question.title })"
                  icon="dots-vertical"
                  icon-only
                  variant="ghost"
                  size="sm"
                >
                  <UiDropdownItem icon="edit" @select="openQuestion(category, question)">
                    {{ t('faq.content.action_edit') }}
                  </UiDropdownItem>
                  <UiDropdownSeparator />
                  <UiDropdownItem
                    icon="arrow-up"
                    :disabled="question.position <= 1"
                    @select="tree.moveQuestion(question, category, question.position - 1)"
                  >
                    {{ t('faq.content.action_up') }}
                  </UiDropdownItem>
                  <UiDropdownItem
                    icon="arrow-down"
                    :disabled="question.position >= category.questions.length"
                    @select="tree.moveQuestion(question, category, question.position + 1)"
                  >
                    {{ t('faq.content.action_down') }}
                  </UiDropdownItem>
                  <template v-if="otherCategories(category).length">
                    <UiDropdownLabel separator>{{ t('faq.content.action_move_to') }}</UiDropdownLabel>
                    <UiDropdownItem
                      v-for="target in otherCategories(category)"
                      :key="target.id"
                      icon="folder"
                      @select="moveQuestionTo(question, target)"
                    >
                      {{ target.title }}
                    </UiDropdownItem>
                  </template>
                  <template v-if="permissions.disable || permissions.delete">
                    <UiDropdownSeparator />
                    <UiDropdownItem
                      v-if="permissions.disable"
                      :icon="question.disabled ? 'check-circle' : 'ban'"
                      @select="tree.toggleQuestion(question)"
                    >
                      {{ question.disabled ? t('faq.content.action_enable') : t('faq.content.action_disable') }}
                    </UiDropdownItem>
                    <UiDropdownItem
                      v-if="permissions.delete"
                      icon="trash"
                      danger
                      @select="tree.deleteQuestion(question)"
                    >
                      {{ t('faq.content.action_delete') }}
                    </UiDropdownItem>
                  </template>
                </UiDropdown>
              </template>
            </li>
            <li v-if="questionLine && newQuestionIn(category)" class="border-b border-line py-3.5" data-faq-line>
              <FaqQuestionEditor :tree="tree" :line="questionLine" :category="category" :read-only="false" />
            </li>
            <li
              v-if="!category.questions.length && !newQuestionIn(category)"
              class="border-b border-line px-2 py-3 text-sm text-fg-subtle"
            >
              {{ t('faq.content.empty_category') }}
            </li>
          </ol>
          <button
            v-if="!readOnly && !newQuestionIn(category)"
            type="button"
            class="mt-1.5 inline-flex h-8 items-center gap-1.5 rounded-sm border-0 bg-transparent px-2 text-sm font-semibold text-primary hover:bg-primary-soft"
            @click="addQuestion(category)"
          >
            <UiIcon name="plus" class="size-4" />
            {{ t('faq.content.add_question') }}
          </button>
        </div>
      </li>
      <li v-if="categoryLine && isLine('category', null)" data-faq-line>
        <FaqCategoryEditor :tree="tree" :line="categoryLine" />
      </li>
    </ol>
    <p v-if="!categories.length && !isLine('category', null)" class="m-0 text-sm text-fg-subtle">
      {{ t('faq.content.empty') }}
    </p>
  </div>
</template>
