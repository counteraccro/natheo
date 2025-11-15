<script>
/**
 * Permet de faire une recherche globale dans le CMS
 * @author Gourdon Aymeric
 * @version 1.0
 */
import axios from 'axios';
import TabSearchResult from '../../../Components/Global/Search/TabSearchResult.vue';
import SkeletonSearchResult from '@/vue/Components/Skeleton/SearchResult.vue';

export default {
  name: 'GlobalSearch',
  components: { SkeletonSearchResult, TabSearchResult },
  props: {
    search: String,
    translate: Object,
    urls: Object,
    limit: Number,
    page: Number,
  },
  emits: [],
  data() {
    return {
      total: 0,
      loading: {
        page: false,
        menu: false,
        faq: false,
        tag: false,
        user: false,
      },
      results: {
        page: null,
        menu: null,
        faq: null,
        tag: null,
        user: null,
      },
      paginate: {
        page: null,
        menu: null,
        faq: null,
        tag: null,
        user: null,
      },
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
    changePage(entity, page, limit) {
      this.globalSearch(entity, this.search, page, limit, true);
    },

    globalSearch(entity, search, page, limit, reload) {
      this.loading[entity] = true;
      axios
        .get(this.urls.searchPage + '/' + entity + '/' + page + '/' + limit + '/' + search, {})
        .then((response) => {
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
};
</script>

<template>
  <div class="float-end mt-2 text-sm text-[var(--text-secondary)]" v-if="this.total !== 0">
    {{ this.total }} {{ this.translate.totalResult }} <b>{{ this.search }}</b>
  </div>
  <div class="float-end mt-2" v-else>
    {{ this.translate.totalNoResult }} <b>{{ this.search }}</b>
  </div>

  <div class="mb-4 mt-4 border-b border-gray-200 dark:border-gray-700" id="tab-search">
    <ul
      class="flex flex-wrap -mb-px text-sm font-medium text-center"
      id="default-styled-tab"
      data-tabs-toggle="#tab-search-content"
      data-tabs-active-classes="text-[var(--primary)] hover:text-[var(--primary-hover)] border-[var(--primary)] bg-[var(--primary-lighter)]"
      data-tabs-inactive-classes="dark:border-transparent text-gray-500 hover:text-gray-600 dark:text-gray-400 border-gray-100 hover:border-gray-300 dark:border-gray-700 dark:hover:text-gray-300"
      role="tablist"
    >
      <li class="me-2" role="presentation">
        <button
          class="inline-block ps-4 pt-2 pe-4 pb-2 border-b-2 rounded-t-sm text-[var(--primary)] hover:text-[var(--primary-hover)] border-[var(--primary)] bg-[var(--primary-lighter)]"
          id="nav-0-tab"
          data-tabs-target="#tab-page"
          type="button"
          role="tab"
          :aria-controls="this.translate.ongletPage.onglet"
          aria-selected="true"
        >
          {{ this.translate.ongletPage.onglet }}
          <svg
            v-if="this.loading.page"
            aria-hidden="true"
            class="w-4 h-4 ml-2 text-neutral-tertiary animate-spin fill-[var(--primary)] inline"
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
          <span
            v-if="!this.loading.page && this.results.page !== null"
            class="ml-2 badge rounded-pill bg-[var(--primary)]"
          >
            {{ this.results.page.total }}
          </span>
        </button>
      </li>
      <li class="me-2" role="presentation">
        <button
          class="inline-block ps-4 pt-2 pe-4 pb-2 border-b-2 rounded-t-sm text-gray-500 hover:text-gray-600 dark:text-gray-400 border-gray-100 hover:border-gray-300 dark:border-gray-700 dark:hover:text-gray-300"
          id="nav-1-tab"
          data-tabs-target="#tab-menu"
          type="button"
          role="tab"
          :aria-controls="this.translate.ongletMenu.onglet"
          aria-selected="false"
        >
          {{ this.translate.ongletMenu.onglet }}
          <svg
            v-if="this.loading.menu"
            aria-hidden="true"
            class="w-4 h-4 ml-2 text-neutral-tertiary animate-spin fill-[var(--primary)] inline"
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
          <span
            v-if="!this.loading.menu && this.results.menu !== null"
            class="ml-2 badge rounded-pill bg-[var(--primary)]"
          >
            {{ this.results.menu.total }}
          </span>
        </button>
      </li>
      <li class="me-2" role="presentation">
        <button
          class="inline-block ps-4 pt-2 pe-4 pb-2 border-b-2 rounded-t-sm text-gray-500 hover:text-gray-600 dark:text-gray-400 border-gray-100 hover:border-gray-300 dark:border-gray-700 dark:hover:text-gray-300"
          id="nav-2-tab"
          data-tabs-target="#tab-faq"
          type="button"
          role="tab"
          :aria-controls="this.translate.ongletFaq.onglet"
          aria-selected="false"
        >
          {{ this.translate.ongletFaq.onglet }}
          <svg
            v-if="this.loading.faq"
            aria-hidden="true"
            class="w-4 h-4 ml-2 text-neutral-tertiary animate-spin fill-[var(--primary)] inline"
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
          <span
            v-if="!this.loading.faq && this.results.faq !== null"
            class="ml-2 badge rounded-pill bg-[var(--primary)]"
          >
            {{ this.results.faq.total }}
          </span>
        </button>
      </li>
      <li class="me-2" role="presentation">
        <button
          class="inline-block ps-4 pt-2 pe-4 pb-2 border-b-2 rounded-t-sm text-gray-500 hover:text-gray-600 dark:text-gray-400 border-gray-100 hover:border-gray-300 dark:border-gray-700 dark:hover:text-gray-300"
          id="nav-3-tab"
          data-tabs-target="#tab-tag"
          type="button"
          role="tab"
          :aria-controls="this.translate.ongletTag.onglet"
          aria-selected="false"
        >
          {{ this.translate.ongletTag.onglet }}
          <svg
            v-if="this.loading.tag"
            aria-hidden="true"
            class="w-4 h-4 ml-2 text-neutral-tertiary animate-spin fill-[var(--primary)] inline"
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
          <span
            v-if="!this.loading.tag && this.results.tag !== null"
            class="ml-2 badge rounded-pill bg-[var(--primary)]"
          >
            {{ this.results.tag.total }}
          </span>
        </button>
      </li>
      <li class="me-2" role="presentation">
        <button
          class="inline-block ps-4 pt-2 pe-4 pb-2 border-b-2 rounded-t-sm text-gray-500 hover:text-gray-600 dark:text-gray-400 border-gray-100 hover:border-gray-300 dark:border-gray-700 dark:hover:text-gray-300"
          id="nav-4-tab"
          data-tabs-target="#tab-user"
          type="button"
          role="tab"
          :aria-controls="this.translate.ongletUser.onglet"
          aria-selected="false"
        >
          {{ this.translate.ongletUser.onglet }}
          <svg
            v-if="this.loading.user"
            aria-hidden="true"
            class="w-4 h-4 ml-2 text-neutral-tertiary animate-spin fill-[var(--primary)] inline"
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
          <span
            v-if="!this.loading.user && this.results.user !== null"
            class="ml-2 badge rounded-pill bg-[var(--primary)]"
          >
            {{ this.results.user.total }}
          </span>
        </button>
      </li>
    </ul>
  </div>

  <div id="tab-search-content">
    <div class="hidden" id="tab-page" role="tabpanel">
      <div v-if="this.loading.page">
        <skeleton-search-result :rows="this.limit" />
      </div>
      <div v-else>
        <h2 class="text-xl font-bold">{{ this.translate.ongletPage.description }}</h2>

        <p class="text-sm text-[var(--text-secondary)]">
          <span v-if="this.results.page !== null">
            {{ this.results.page.total }} {{ this.translate.ongletPage.title }}
          </span>
          <span v-else>0 {{ this.translate.ongletPage.title }}</span>
        </p>

        <div v-if="this.results.page === null && !this.loading.page">
          {{ this.translate.ongletPage.noResult }}
        </div>
        <div v-if="this.results.page !== null" class="mt-2">
          <tab-search-result
            key="1"
            :result="this.results.page"
            :translate="this.translate.ongletPage"
            :translate-paginate="this.translate.paginate"
            :paginate="this.paginate.page"
            :entity="'page'"
            @change-page-event="this.changePage"
          >
          </tab-search-result>
        </div>
      </div>
    </div>

    <div class="hidden" id="tab-menu" role="tabpanel">
      <div v-if="this.loading.menu">
        <skeleton-search-result :rows="this.limit" />
      </div>
      <div v-else>
        <h2 class="text-xl font-bold">{{ this.translate.ongletMenu.description }}</h2>
        <p class="text-sm text-[var(--text-secondary)]">
          <span v-if="this.results.menu !== null">
            {{ this.results.menu.total }} {{ this.translate.ongletMenu.title }}
          </span>
          <span v-else>0 {{ this.translate.ongletMenu.title }}</span>
        </p>

        <div v-if="this.results.menu === null && !this.loading.menu">
          {{ this.translate.ongletMenu.noResult }}
        </div>
        <div v-if="this.results.menu !== null" class="mt-2">
          <tab-search-result
            key="3"
            :result="this.results.menu"
            :translate="this.translate.ongletMenu"
            :translate-paginate="this.translate.paginate"
            :paginate="this.paginate.menu"
            :entity="'menu'"
            @change-page-event="this.changePage"
          >
          </tab-search-result>
        </div>
      </div>
    </div>

    <div class="hidden" id="tab-faq" role="tabpanel">
      <div v-if="this.loading.faq">
        <skeleton-search-result :rows="this.limit" />
      </div>
      <div v-else>
        <h2 class="text-xl font-bold">{{ this.translate.ongletFaq.description }}</h2>
        <p class="text-sm text-[var(--text-secondary)]">
          <span v-if="this.results.faq !== null">
            {{ this.results.faq.total }} {{ this.translate.ongletFaq.title }}
          </span>
          <span v-else>0 {{ this.translate.ongletFaq.title }}</span>
        </p>

        <div v-if="this.results.faq === null && !this.loading.faq">
          {{ this.translate.ongletFaq.noResult }}
        </div>
        <div v-if="this.results.faq !== null">
          <tab-search-result
            key="4"
            :result="this.results.faq"
            :translate="this.translate.ongletFaq"
            :translate-paginate="this.translate.paginate"
            :paginate="this.paginate.faq"
            :entity="'faq'"
            @change-page-event="this.changePage"
          >
          </tab-search-result>
        </div>
      </div>
    </div>

    <div class="hidden" id="tab-tag" role="tabpanel">
      <div v-if="this.loading.tag">
        <skeleton-search-result :rows="this.limit" />
      </div>
      <div v-else>
        <h2 class="text-xl font-bold">{{ this.translate.ongletTag.description }}</h2>
        <p class="text-sm text-[var(--text-secondary)]">
          <span v-if="this.results.tag !== null">
            {{ this.results.tag.total }} {{ this.translate.ongletTag.title }}
          </span>
          <span v-else>0 {{ this.translate.ongletTag.title }}</span>
        </p>

        <div v-if="this.results.tag === null && !this.loading.tag">
          {{ this.translate.ongletTag.noResult }}
        </div>

        <div v-if="this.results.tag !== null">
          <tab-search-result
            key="5"
            :result="this.results.tag"
            :translate="this.translate.ongletTag"
            :translate-paginate="this.translate.paginate"
            :paginate="this.paginate.tag"
            :entity="'tag'"
            @change-page-event="this.changePage"
          >
          </tab-search-result>
        </div>
      </div>
    </div>

    <div class="hidden" id="tab-user" role="tabpanel">
      <div v-if="this.loading.user">
        <skeleton-search-result :rows="this.limit" />
      </div>
      <div v-else>
        <h2 class="text-xl font-bold">{{ this.translate.ongletUser.description }}</h2>
        <p class="text-sm text-[var(--text-secondary)]">
          <span v-if="this.results.user !== null">
            {{ this.results.user.total }} {{ this.translate.ongletUser.title }}
          </span>
          <span v-else>0 {{ this.translate.ongletUser.title }}</span>
        </p>

        <div v-if="this.results.user === null && !this.loading.tag">
          {{ this.translate.ongletTag.noResult }}
        </div>

        <div v-if="this.results.user !== null">
          <tab-search-result
            key="6"
            :result="this.results.user"
            :translate="this.translate.ongletUser"
            :translate-paginate="this.translate.paginate"
            :paginate="this.paginate.user"
            :entity="'user'"
            @change-page-event="this.changePage"
          >
          </tab-search-result>
        </div>
      </div>
    </div>
  </div>
</template>
