<script lang="ts">
import { defineComponent, PropType } from 'vue';
import {
  Locales,
  Page,
  PageContentItem,
  PageContentTranslationItem,
  PageData,
  PageTranslations,
  Urls,
} from '@/ts/Page/type';
import axios from 'axios';
import MarkdownEditor from '@/vue/Components/Global/MarkdownEditor/MarkdownEditor.vue';
import Modal from '@/vue/Components/Global/Modal.vue';

interface ContentType {
  list: Record<string, string>;
  label: string;
  help: string;
  selected: number;
}

interface BlockNeighbors {
  left: number | null;
  right: number | null;
  up: number | null;
  down: number | null;
}

export default defineComponent({
  name: 'PageContentBlock',
  components: { MarkdownEditor, Modal },
  props: {
    translate: {
      type: Object as PropType<PageTranslations>,
      required: true,
    },
    page: {
      type: Object as PropType<Page>,
      required: true,
    },
    currentLocale: {
      type: String as PropType<string>,
      required: true,
    },
    urls: {
      type: Object as PropType<Urls>,
      required: true,
    },
    pageDatas: {
      type: Object as PropType<PageData>,
      required: true,
    },
    locales: {
      type: Object as PropType<Locales>,
      required: true,
    },
    renderBlockId: {
      type: Number as PropType<number>,
      required: true,
    },
    restoreCount: {
      type: Number as PropType<number>,
      required: true,
    },
    neighbors: {
      type: Object as PropType<BlockNeighbors>,
      required: true,
    },
  },
  emits: ['update-page-contents', 'swap-blocks'],
  data() {
    return {
      showModalNew: false as boolean,
      showModalRemove: false as boolean,
      idSelectContent: 0 as number,
      idSelectTypeContent: 0 as number,
      contentType: null as ContentType | null,
      loading: false as boolean,
    };
  },
  computed: {
    modalNewId(): string {
      return `modal-new-content-${this.renderBlockId}`;
    },
    modalRemoveId(): string {
      return `modal-remove-content-${this.renderBlockId}`;
    },
    blockContent(): PageContentItem | undefined {
      return this.page.pageContents.find((c) => c.renderBlock === this.renderBlockId);
    },
    blockText(): string {
      return this.blockContent?.pageContentTranslations.find((t) => t.locale === this.currentLocale)?.text ?? '';
    },
    isEmpty(): boolean {
      return this.blockContent === undefined;
    },
    isTypeText(): boolean {
      return this.blockContent?.type === 1;
    },
    isTypeFaq(): boolean {
      return this.blockContent?.type === 2;
    },
    isTypeListing(): boolean {
      return this.blockContent?.type === 3;
    },
  },
  methods: {
    openModalNew() {
      this.resetNewContent();
      this.showModalNew = true;
    },
    closeModalNew() {
      this.showModalNew = false;
      this.resetNewContent();
    },
    openModalRemove() {
      this.showModalRemove = true;
    },
    closeModalRemove() {
      this.showModalRemove = false;
    },
    resetNewContent() {
      this.idSelectContent = 0;
      this.idSelectTypeContent = 0;
      this.contentType = null;
    },
    swapUp() {
      if (this.neighbors.up !== null) this.$emit('swap-blocks', this.renderBlockId, this.neighbors.up);
    },
    swapDown() {
      if (this.neighbors.down !== null) this.$emit('swap-blocks', this.renderBlockId, this.neighbors.down);
    },
    swapLeft() {
      if (this.neighbors.left !== null) this.$emit('swap-blocks', this.renderBlockId, this.neighbors.left);
    },
    swapRight() {
      if (this.neighbors.right !== null) this.$emit('swap-blocks', this.renderBlockId, this.neighbors.right);
    },
    loadContentType() {
      if (this.idSelectContent <= 1) {
        this.contentType = null;
        this.idSelectTypeContent = 0;
        return;
      }
      this.loading = true;
      axios
        .get(this.urls.liste_content_by_id + '/' + this.idSelectContent)
        .then((response) => {
          this.contentType = {
            list: response.data.list,
            label: response.data.label,
            help: response.data.help,
            selected: response.data.selected,
          };
          this.idSelectTypeContent = response.data.selected;
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;
        });
    },
    addContent() {
      if (this.idSelectContent <= 0) return;

      const newContent: PageContentItem = {
        id: null,
        page: this.page.id,
        renderOrder: 1,
        type: this.idSelectContent,
        typeId: this.idSelectContent > 1 ? this.idSelectTypeContent : null,
        renderBlock: this.renderBlockId,
        pageContentTranslations:
          this.idSelectContent === 1
            ? this.locales.locales.map(
                (locale): PageContentTranslationItem => ({
                  id: null,
                  pageContent: null,
                  locale,
                  text: `[${locale}] ${this.translate.page_content.page_content_block.default_text}`,
                })
              )
            : [],
      };

      this.$emit('update-page-contents', [...this.page.pageContents, newContent]);
      this.closeModalNew();
    },
    removeContent() {
      this.$emit(
        'update-page-contents',
        this.page.pageContents.filter((c) => c.renderBlock !== this.renderBlockId)
      );
      this.closeModalRemove();
    },
    onTextChange(_id: string, value: string) {
      if (!this.blockContent) return;
      const translation = this.blockContent.pageContentTranslations.find((t) => t.locale === this.currentLocale);
      if (translation) {
        translation.text = value;
      } else {
        this.blockContent.pageContentTranslations.push({
          id: null,
          pageContent: this.blockContent.id,
          locale: this.currentLocale,
          text: value,
        });
      }
    },
  },
});
</script>

<template>
  <div class="card rounded-lg overflow-visible" style="border-color: var(--border-color)">
    <div class="px-4 py-3 border-b flex items-center justify-between" style="border-color: var(--border-color)">
      <span class="text-xs font-semibold" style="color: var(--text-secondary)">
        {{ translate.page_content.page_content_block.bloc }} {{ renderBlockId }}
      </span>
      <div class="flex items-center gap-1">
        <button v-if="neighbors.up !== null" type="button" class="btn btn-xs btn-outline-dark" @click="swapUp">
          <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
          </svg>
        </button>
        <button v-if="neighbors.left !== null" type="button" class="btn btn-xs btn-outline-dark" @click="swapLeft">
          <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <button v-if="neighbors.right !== null" type="button" class="btn btn-xs btn-outline-dark" @click="swapRight">
          <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
        <button v-if="neighbors.down !== null" type="button" class="btn btn-xs btn-outline-dark" @click="swapDown">
          <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>
        <button v-if="!isEmpty" type="button" class="btn btn-xs btn-danger" @click="openModalRemove">
          <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
          {{ translate.page_content.page_content_block.btn_delete_content }}
        </button>
      </div>
    </div>

    <div class="p-4">
      <div
        v-if="isEmpty"
        class="flex flex-col items-center justify-center gap-3 rounded-lg border-2 border-dashed py-10 cursor-pointer transition-all"
        style="border-color: var(--border-dark); color: var(--text-secondary); min-height: 120px"
        @mouseenter="
          ($event.currentTarget as HTMLElement).style.borderColor = 'var(--primary)';
          ($event.currentTarget as HTMLElement).style.backgroundColor = 'var(--primary-lighter)';
        "
        @mouseleave="
          ($event.currentTarget as HTMLElement).style.borderColor = 'var(--border-dark)';
          ($event.currentTarget as HTMLElement).style.backgroundColor = '';
        "
        @click="openModalNew"
      >
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4" />
        </svg>
        <span class="text-sm font-medium">{{ translate.page_content.page_content_block.btn_new_content }}</span>
      </div>

      <div v-else>
        <div v-if="isTypeText">
          <MarkdownEditor
            :key="'content-' + renderBlockId + '-' + currentLocale + '-' + restoreCount"
            :me-id="'content-' + renderBlockId + '-' + currentLocale"
            :me-value="blockText"
            :me-rows="14"
            :me-translate="translate.page_content.page_content_block.markdown"
            :me-key-words="[]"
            :me-save="false"
            :me-preview="false"
            @editor-value-change="onTextChange"
          />
        </div>

        <div v-else-if="isTypeFaq" class="flex items-center gap-3 py-6 justify-center">
          <svg class="w-8 h-8" style="color: var(--primary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="1.5"
              d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          <div>
            <p class="text-sm font-semibold" style="color: var(--text-primary)">FAQ</p>
            <p class="text-xs" style="color: var(--text-secondary)">
              {{ translate.page_content.page_content_block.faq_id }} : {{ blockContent!.typeId }}
            </p>
          </div>
        </div>

        <div v-else-if="isTypeListing" class="flex items-center gap-3 py-6 justify-center">
          <svg class="w-8 h-8" style="color: var(--primary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="1.5"
              d="M4 6h16M4 10h16M4 14h16M4 18h16"
            />
          </svg>
          <div>
            <p class="text-sm font-semibold" style="color: var(--text-primary)">Listing</p>
            <p class="text-xs" style="color: var(--text-secondary)">
              {{ translate.page_content.page_content_block.listing_id }} : {{ blockContent!.typeId }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <Modal :id="modalNewId" :show="showModalNew" @close-modal="closeModalNew">
      <template #title>{{ translate.page_content.page_content_block.modale_new_title }}</template>
      <template #body>
        <div class="form-group">
          <label :for="'list-choice-content-' + renderBlockId" class="form-label">
            {{ translate.page_content.page_content_block.modale_new_choice_label }}
          </label>
          <select
            :id="'list-choice-content-' + renderBlockId"
            class="form-input"
            v-model="idSelectContent"
            @change="loadContentType"
          >
            <option :value="0">---</option>
            <option v-for="(value, key) in pageDatas.list_content" :key="key" :value="parseInt(key as string)">
              {{ value }}
            </option>
          </select>
          <div class="form-text">{{ translate.page_content.page_content_block.modale_new_choice_info }}</div>
        </div>

        <div v-if="loading" class="flex justify-center py-4">
          <svg
            class="w-6 h-6 animate-spin"
            style="color: var(--primary)"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
            />
          </svg>
        </div>

        <div v-else-if="contentType" class="form-group">
          <label :for="'list-choice-type-content-' + renderBlockId" class="form-label">
            {{ contentType.label }}
          </label>
          <select :id="'list-choice-type-content-' + renderBlockId" class="form-input" v-model="idSelectTypeContent">
            <option v-for="(value, key) in contentType.list" :key="key" :value="parseInt(key as string)">
              {{ value }}
            </option>
          </select>
          <div class="form-text">{{ contentType.help }}</div>
        </div>
      </template>
      <template #footer>
        <button type="button" class="btn btn-sm btn-outline-dark me-2" @click="closeModalNew">
          {{ translate.page_content.page_content_block.modale_new_btn_cancel }}
        </button>
        <button type="button" class="btn btn-sm btn-primary" :disabled="idSelectContent <= 0" @click="addContent">
          {{ translate.page_content.page_content_block.modale_new_btn_new }}
        </button>
      </template>
    </Modal>

    <Modal :id="modalRemoveId" :show="showModalRemove" @close-modal="closeModalRemove">
      <template #title>{{ translate.page_content.page_content_block.modale_remove_title }}</template>
      <template #body>
        <p class="text-sm" style="color: var(--text-secondary)">
          {{ translate.page_content.page_content_block.modale_remove_body }}
        </p>
      </template>
      <template #footer>
        <button type="button" class="btn btn-sm btn-outline-dark me-2" @click="closeModalRemove">
          {{ translate.page_content.page_content_block.modale_remove_btn_cancel }}
        </button>
        <button type="button" class="btn btn-sm btn-danger" @click="removeContent">
          {{ translate.page_content.page_content_block.modale_remove_btn_confirm }}
        </button>
      </template>
    </Modal>
  </div>
</template>

<style scoped></style>
