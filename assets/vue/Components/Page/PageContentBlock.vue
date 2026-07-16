<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { Page, PageContentItem, PageData, PageTranslations, Urls } from '@/ts/Page/type';
import axios from 'axios';
import { initFlowbite, Modal } from 'flowbite';

interface ContentType {
  list: Record<string, string>;
  label: string;
  help: string;
  selected: number;
}

export default defineComponent({
  name: 'PageContentBlock',
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
    renderBlockId: {
      type: Number as PropType<number>,
      required: true,
    },
  },
  emits: ['update-page-contents'],
  data() {
    return {
      modalNew: null as InstanceType<typeof Modal> | null,
      modalRemove: null as InstanceType<typeof Modal> | null,
      idSelectContent: 0 as number,
      idSelectTypeContent: 0 as number,
      contentType: null as ContentType | null,
      loading: false as boolean,
      idConfirmRemove: 0 as number,
    };
  },
  mounted() {
    this.$nextTick(() => {
      initFlowbite();
      const elNew = document.getElementById(this.modalNewId);
      const elRemove = document.getElementById(this.modalRemoveId);
      if (elNew) this.modalNew = new Modal(elNew);
      if (elRemove) this.modalRemove = new Modal(elRemove);
    });
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
  },
  methods: {
    openModalNew() {
      this.resetNewContent();
      this.modalNew?.show();
    },
    closeModalNew() {
      this.modalNew?.hide();
      this.resetNewContent();
    },
    openModalRemove(id: number) {
      this.idConfirmRemove = id;
      this.modalRemove?.show();
    },
    closeModalRemove() {
      this.modalRemove?.hide();
      this.idConfirmRemove = 0;
    },
    resetNewContent() {
      this.idSelectContent = 0;
      this.idSelectTypeContent = 0;
      this.contentType = null;
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
      if (this.idSelectContent <= 0) {
        return;
      }

      axios
        .post(this.urls.new_content, {
          type: this.idSelectContent,
          type_id: this.idSelectTypeContent,
          render_block: this.renderBlockId,
          page_id: this.page.id,
        })
        .catch((error) => {
          console.error(error);
        });
    },
    removeContent() {
      axios.delete(this.urls.new_content + '/' + this.idConfirmRemove).catch((error) => {
        console.error(error);
      });
    },
  },
});
</script>

<template>
  <div>
    <div
      v-if="isEmpty"
      class="flex flex-col items-center justify-center gap-3 rounded-lg border-2 border-dashed py-10 cursor-pointer transition-all"
      style="border-color: var(--border-dark); color: var(--text-secondary); min-height: 160px"
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

    <div v-else class="card rounded-lg overflow-visible" style="border-color: var(--border-color)">
      <div class="px-4 py-3 border-b flex items-center justify-between" style="border-color: var(--border-color)">
        <span class="text-xs font-semibold" style="color: var(--text-secondary)">
          {{ translate.page_content.page_content_block.bloc }} {{ renderBlockId }}
        </span>
        <button type="button" class="btn btn-xs btn-danger" @click="openModalRemove(blockContent!.id)">
          <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
          {{ translate.page_content.page_content_block.btn_delete_content }}
        </button>
      </div>
      <div class="p-4">
        <p class="text-sm" style="color: var(--text-secondary)">{{ blockText.substring(0, 120) }}...</p>
      </div>
    </div>

    <!-- Modale ajout -->
    <div
      :id="modalNewId"
      tabindex="-1"
      aria-hidden="true"
      class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full"
    >
      <div class="relative p-4 w-full max-w-lg max-h-full">
        <div class="relative rounded-lg shadow" style="background-color: var(--bg-card)">
          <div
            class="flex items-center justify-between p-4 border-b rounded-t"
            style="border-color: var(--border-color)"
          >
            <h3 class="text-base font-semibold" style="color: var(--text-primary)">
              {{ translate.page_content.page_content_block.modale_new_title }}
            </h3>
            <button type="button" class="btn btn-sm btn-outline-dark" @click="closeModalNew">
              <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="p-4 flex flex-col gap-4">
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
              <select
                :id="'list-choice-type-content-' + renderBlockId"
                class="form-input"
                v-model="idSelectTypeContent"
              >
                <option v-for="(value, key) in contentType.list" :key="key" :value="parseInt(key as string)">
                  {{ value }}
                </option>
              </select>
              <div class="form-text">{{ contentType.help }}</div>
            </div>
          </div>

          <div class="flex justify-end gap-3 p-4 border-t" style="border-color: var(--border-color)">
            <button type="button" class="btn btn-sm btn-outline-dark" @click="closeModalNew">
              {{ translate.page_content.page_content_block.modale_new_btn_cancel }}
            </button>
            <button type="button" class="btn btn-sm btn-primary" :disabled="idSelectContent <= 0" @click="addContent">
              {{ translate.page_content.page_content_block.modale_new_btn_new }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modale suppression -->
    <div
      :id="modalRemoveId"
      tabindex="-1"
      aria-hidden="true"
      class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full"
    >
      <div class="relative p-4 w-full max-w-md max-h-full">
        <div class="relative rounded-lg shadow" style="background-color: var(--bg-card)">
          <div
            class="flex items-center justify-between p-4 border-b rounded-t"
            style="border-color: var(--border-color)"
          >
            <h3 class="text-base font-semibold" style="color: var(--text-primary)">
              {{ translate.page_content.page_content_block.modale_remove_title }}
            </h3>
            <button type="button" class="btn btn-sm btn-outline-dark" @click="closeModalRemove">
              <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <div class="p-4">
            <p class="text-sm" style="color: var(--text-secondary)">
              {{ translate.page_content.page_content_block.modale_remove_body }}
            </p>
          </div>
          <div class="flex justify-end gap-3 p-4 border-t" style="border-color: var(--border-color)">
            <button type="button" class="btn btn-sm btn-outline-dark" @click="closeModalRemove">
              {{ translate.page_content.page_content_block.modale_remove_btn_cancel }}
            </button>
            <button type="button" class="btn btn-sm btn-danger" @click="removeContent">
              {{ translate.page_content.page_content_block.modale_remove_btn_confirm }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped></style>
