<script lang="ts">
/**
 * Permet de faire une recherche globale dans le CMS
 * @author Gourdon Aymeric
 * @version 2.0
 */
import { defineComponent, type PropType } from 'vue';
import axios from 'axios';
import TabSearchResult from '../../../Components/Global/Search/TabSearchResult.vue';
import SkeletonSearchResult from '@/vue/Components/Skeleton/SearchResult.vue';
import type {
  GlobalSearchEntity,
  GlobalSearchUrls,
  GlobalSearchTranslate,
  GlobalSearchResult,
  GlobalSearchPaginate,
  GlobalSearchResponse,
} from '@/ts/Global/GlobalSearch.type';

export default defineComponent({
  name: 'GlobalSearch',

  components: { SkeletonSearchResult, TabSearchResult },

  props: {
    search: {
      type: String,
      required: true,
    },
    translate: {
      type: Object as PropType<GlobalSearchTranslate>,
      required: true,
    },
    urls: {
      type: Object as PropType<GlobalSearchUrls>,
      required: true,
    },
    limit: {
      type: Number,
      required: true,
    },
    page: {
      type: Number,
      required: true,
    },
  },

  emits: [],

  data() {
    return {
      total: 0 as number,
      loading: {
        page: false,
        menu: false,
        faq: false,
        tag: false,
        user: false,
      } as Record<GlobalSearchEntity, boolean>,
      results: {
        page: null,
        menu: null,
        faq: null,
        tag: null,
        user: null,
      } as Record<GlobalSearchEntity, GlobalSearchResult | null>,
      paginate: {} as Partial<Record<GlobalSearchEntity, GlobalSearchPaginate>>,
    };
  },

  mounted() {
    this.globalSearch('page', this.search, this.page, this.limit, false);
    this.globalSearch('menu', this.search, this.page, this.limit, false);
    this.globalSearch('faq', this.search, this.page, this.limit, false);
    this.globalSearch('tag', this.search, this.page, this.limit, false);
    this.globalSearch('user', this.search, this.page, this.limit, false);
  },

  methods: {
    /**
     * Changement de page pour une entité
     * @param entity
     * @param page
     * @param limit
     */
    changePage(entity: GlobalSearchEntity, page: number, limit: number): void {
      this.globalSearch(entity, this.search, page, limit, true);
    },

    /**
     * Lance la recherche pour une entité
     * @param entity
     * @param search
     * @param page
     * @param limit
     * @param reload
     */
    globalSearch(entity: GlobalSearchEntity, search: string, page: number, limit: number, reload: boolean): void {
      this.loading[entity] = true;
      axios
        .get<GlobalSearchResponse>(
          this.urls.searchPage + '/' + entity + '/' + page + '/' + limit + '/' + encodeURIComponent(search)
        )
        .then((response) => {
          if (response.data.result.error) {
            console.error(response.data.result.error);
          }
          if (response.data.result.total > 0) {
            this.results[entity] = response.data.result;
            this.paginate[entity] = response.data.paginate;
            if (!reload) {
              this.total += response.data.result.total;
            }
          }
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loading[entity] = false;
        });
    },
  },
});
</script>

<template>
  <div class="float-end mt-2 text-sm text-(--text-secondary)" v-if="total !== 0">
    {{ total }} {{ translate.totalResult }} <b>{{ search }}</b>
  </div>
  <div class="float-end mt-2" v-else>
    {{ translate.totalNoResult }} <b>{{ search }}</b>
  </div>

  <div class="mb-4 mt-4 border-b border-gray-200 dark:border-gray-700" id="tab-search">
    <ul
      class="flex flex-wrap -mb-px text-sm font-medium text-center"
      id="default-styled-tab"
      data-tabs-toggle="#tab-search-content"
      data-tabs-active-classes="text-(--primary) hover:text-(--primary-hover) border-(--primary) bg-(--primary-lighter)"
      data-tabs-inactive-classes="dark:border-transparent text-gray-500 hover:text-gray-600 dark:text-gray-400 border-gray-100 hover:border-gray-300 dark:border-gray-700 dark:hover:text-gray-300"
      role="tablist"
    >
      <li class="me-2" role="presentation">
        <button
          class="inline-block ps-4 pt-2 pe-4 border-b-2 rounded-t-sm text-(--primary) hover:text-(--primary-hover) border-(--primary) bg-(--primary-lighter) cursor-pointer"
          :class="results.page === null ? 'pb-3' : 'pb-2'"
          id="nav-0-tab"
          data-tabs-target="#tab-page"
          type="button"
          role="tab"
          :aria-controls="translate.ongletPage.onglet"
          aria-selected="true"
        >
          {{ translate.ongletPage.onglet }}
          <svg
            v-if="loading.page"
            aria-hidden="true"
            class="w-4 h-4 ml-2 text-neutral-tertiary animate-spin fill-(--primary) inline"
            viewBox="0 0 100 101"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
              fill="currentColor"
            />
            <path
              d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
              fill="currentFill"
            />
          </svg>
          <span v-if="!loading.page && results.page !== null" class="ml-2 badge rounded-pill bg-(--primary)">
            {{ results.page.total }}
          </span>
        </button>
      </li>
      <li class="me-2" role="presentation">
        <button
          class="inline-block ps-4 pt-2 pe-4 border-b-2 rounded-t-sm text-gray-500 hover:text-gray-600 dark:text-gray-400 border-gray-100 hover:border-gray-300 dark:border-gray-700 dark:hover:text-gray-300 cursor-pointer"
          :class="results.menu === null ? 'pb-3' : 'pb-2'"
          id="nav-1-tab"
          data-tabs-target="#tab-menu"
          type="button"
          role="tab"
          :aria-controls="translate.ongletMenu.onglet"
          aria-selected="false"
        >
          {{ translate.ongletMenu.onglet }}
          <svg
            v-if="loading.menu"
            aria-hidden="true"
            class="w-4 h-4 ml-2 text-neutral-tertiary animate-spin fill-(--primary) inline"
            viewBox="0 0 100 101"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
              fill="currentColor"
            />
            <path
              d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
              fill="currentFill"
            />
          </svg>
          <span v-if="!loading.menu && results.menu !== null" class="ml-2 badge rounded-pill bg-(--primary)">
            {{ results.menu.total }}
          </span>
        </button>
      </li>
      <li class="me-2" role="presentation">
        <button
          class="inline-block ps-4 pt-2 pe-4 border-b-2 rounded-t-sm text-gray-500 hover:text-gray-600 dark:text-gray-400 border-gray-100 hover:border-gray-300 dark:border-gray-700 dark:hover:text-gray-300 cursor-pointer"
          :class="results.faq === null ? 'pb-3' : 'pb-2'"
          id="nav-2-tab"
          data-tabs-target="#tab-faq"
          type="button"
          role="tab"
          :aria-controls="translate.ongletFaq.onglet"
          aria-selected="false"
        >
          {{ translate.ongletFaq.onglet }}
          <svg
            v-if="loading.faq"
            aria-hidden="true"
            class="w-4 h-4 ml-2 text-neutral-tertiary animate-spin fill-(--primary) inline"
            viewBox="0 0 100 101"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
              fill="currentColor"
            />
            <path
              d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
              fill="currentFill"
            />
          </svg>
          <span v-if="!loading.faq && results.faq !== null" class="ml-2 badge rounded-pill bg-(--primary)">
            {{ results.faq.total }}
          </span>
        </button>
      </li>
      <li class="me-2" role="presentation">
        <button
          class="inline-block ps-4 pt-2 pe-4 border-b-2 rounded-t-sm text-gray-500 hover:text-gray-600 dark:text-gray-400 border-gray-100 hover:border-gray-300 dark:border-gray-700 dark:hover:text-gray-300 cursor-pointer"
          :class="results.tag === null ? 'pb-3' : 'pb-2'"
          id="nav-3-tab"
          data-tabs-target="#tab-tag"
          type="button"
          role="tab"
          :aria-controls="translate.ongletTag.onglet"
          aria-selected="false"
        >
          {{ translate.ongletTag.onglet }}
          <svg
            v-if="loading.tag"
            aria-hidden="true"
            class="w-4 h-4 ml-2 text-neutral-tertiary animate-spin fill-(--primary) inline"
            viewBox="0 0 100 101"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
              fill="currentColor"
            />
            <path
              d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
              fill="currentFill"
            />
          </svg>
          <span v-if="!loading.tag && results.tag !== null" class="ml-2 badge rounded-pill bg-(--primary)">
            {{ results.tag.total }}
          </span>
        </button>
      </li>
      <li class="me-2" role="presentation">
        <button
          class="inline-block ps-4 pt-2 pe-4 border-b-2 rounded-t-sm text-gray-500 hover:text-gray-600 dark:text-gray-400 border-gray-100 hover:border-gray-300 dark:border-gray-700 dark:hover:text-gray-300 cursor-pointer"
          :class="results.user === null ? 'pb-3' : 'pb-2'"
          id="nav-4-tab"
          data-tabs-target="#tab-user"
          type="button"
          role="tab"
          :aria-controls="translate.ongletUser.onglet"
          aria-selected="false"
        >
          {{ translate.ongletUser.onglet }}
          <svg
            v-if="loading.user"
            aria-hidden="true"
            class="w-4 h-4 ml-2 text-neutral-tertiary animate-spin fill-(--primary) inline"
            viewBox="0 0 100 101"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
              fill="currentColor"
            />
            <path
              d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
              fill="currentFill"
            />
          </svg>
          <span v-if="!loading.user && results.user !== null" class="ml-2 badge rounded-pill bg-(--primary)">
            {{ results.user.total }}
          </span>
        </button>
      </li>
    </ul>
  </div>

  <div id="tab-search-content">
    <div class="hidden" id="tab-page" role="tabpanel">
      <div v-if="loading.page">
        <skeleton-search-result :rows="limit" />
      </div>
      <div v-else>
        <h2 class="text-xl font-bold">{{ translate.ongletPage.description }}</h2>

        <p class="text-sm text-(--text-secondary)">
          <span v-if="results.page !== null"> {{ results.page.total }} {{ translate.ongletPage.title }} </span>
          <span v-else>0 {{ translate.ongletPage.title }}</span>
        </p>

        <div
          v-if="results.page === null && !loading.page"
          class="mt-4 text-center text-sm text-(--text-secondary) italic"
        >
          {{ translate.ongletPage.noResult }}
        </div>
        <div v-if="results.page !== null" class="mt-2">
          <tab-search-result
            key="1"
            :result="results.page"
            :translate="translate.ongletPage"
            :translate-paginate="translate.paginate"
            :paginate="paginate.page"
            :entity="'page'"
            @change-page-event="changePage"
          >
          </tab-search-result>
        </div>
      </div>
    </div>

    <div class="hidden" id="tab-menu" role="tabpanel">
      <div v-if="loading.menu">
        <skeleton-search-result :rows="limit" />
      </div>
      <div v-else>
        <h2 class="text-xl font-bold">{{ translate.ongletMenu.description }}</h2>
        <p class="text-sm text-(--text-secondary)">
          <span v-if="results.menu !== null"> {{ results.menu.total }} {{ translate.ongletMenu.title }} </span>
          <span v-else>0 {{ translate.ongletMenu.title }}</span>
        </p>

        <div
          v-if="results.menu === null && !loading.menu"
          class="mt-4 text-center text-sm text-(--text-secondary) italic"
        >
          {{ translate.ongletMenu.noResult }}
        </div>
        <div v-if="results.menu !== null" class="mt-2">
          <tab-search-result
            key="3"
            :result="results.menu"
            :translate="translate.ongletMenu"
            :translate-paginate="translate.paginate"
            :paginate="paginate.menu"
            :entity="'menu'"
            @change-page-event="changePage"
          >
          </tab-search-result>
        </div>
      </div>
    </div>

    <div class="hidden" id="tab-faq" role="tabpanel">
      <div v-if="loading.faq">
        <skeleton-search-result :rows="limit" />
      </div>
      <div v-else>
        <h2 class="text-xl font-bold">{{ translate.ongletFaq.description }}</h2>
        <p class="text-sm text-(--text-secondary)">
          <span v-if="results.faq !== null"> {{ results.faq.total }} {{ translate.ongletFaq.title }} </span>
          <span v-else>0 {{ translate.ongletFaq.title }}</span>
        </p>

        <div
          v-if="results.faq === null && !loading.faq"
          class="mt-4 text-center text-sm text-(--text-secondary) italic"
        >
          {{ translate.ongletFaq.noResult }}
        </div>
        <div v-if="results.faq !== null" class="mt-2">
          <tab-search-result
            key="4"
            :result="results.faq"
            :translate="translate.ongletFaq"
            :translate-paginate="translate.paginate"
            :paginate="paginate.faq"
            :entity="'faq'"
            @change-page-event="changePage"
          >
          </tab-search-result>
        </div>
      </div>
    </div>

    <div class="hidden" id="tab-tag" role="tabpanel">
      <div v-if="loading.tag">
        <skeleton-search-result :rows="limit" />
      </div>
      <div v-else>
        <h2 class="text-xl font-bold">{{ translate.ongletTag.description }}</h2>
        <p class="text-sm text-(--text-secondary)">
          <span v-if="results.tag !== null"> {{ results.tag.total }} {{ translate.ongletTag.title }} </span>
          <span v-else>0 {{ translate.ongletTag.title }}</span>
        </p>

        <div
          v-if="results.tag === null && !loading.tag"
          class="mt-4 text-center text-sm text-(--text-secondary) italic"
        >
          {{ translate.ongletTag.noResult }}
        </div>

        <div v-if="results.tag !== null" class="mt-2">
          <tab-search-result
            key="5"
            :result="results.tag"
            :translate="translate.ongletTag"
            :translate-paginate="translate.paginate"
            :paginate="paginate.tag"
            :entity="'tag'"
            @change-page-event="changePage"
          >
          </tab-search-result>
        </div>
      </div>
    </div>

    <div class="hidden" id="tab-user" role="tabpanel">
      <div v-if="loading.user">
        <skeleton-search-result :rows="limit" />
      </div>
      <div v-else>
        <h2 class="text-xl font-bold">{{ translate.ongletUser.description }}</h2>
        <p class="text-sm text-(--text-secondary)">
          <span v-if="results.user !== null"> {{ results.user.total }} {{ translate.ongletUser.title }} </span>
          <span v-else>0 {{ translate.ongletUser.title }}</span>
        </p>

        <div
          v-if="results.user === null && !loading.user"
          class="mt-4 text-center text-sm text-(--text-secondary) italic"
        >
          {{ translate.ongletUser.noResult }}
        </div>

        <div v-if="results.user !== null" class="mt-2">
          <tab-search-result
            key="6"
            :result="results.user"
            :translate="translate.ongletUser"
            :translate-paginate="translate.paginate"
            :paginate="paginate.user"
            :entity="'user'"
            @change-page-event="changePage"
          >
          </tab-search-result>
        </div>
      </div>
    </div>
  </div>
</template>
