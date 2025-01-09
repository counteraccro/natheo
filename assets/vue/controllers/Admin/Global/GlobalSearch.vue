<script>/**
 * Permet de faire une recherche globale dans le CMS
 * @author Gourdon Aymeric
 * @version 1.0
 */
import axios from "axios";
import TabPageSearchResult from "../../../Components/Global/Search/TabPageSearchResult.vue";


export default {
  name: 'GlobalSearch',
  components: {TabPageSearchResult},
  props: {
    search: String,
    translate: Object,
    urls: Object,
    limit: Number,
    page: Number
  },
  emits: [],
  data() {
    return {
      total: 0,
      loading: {
        page: false,
        menu: false
      },
      results: {
        page: null,
        menu: null,
      },
      paginate: {
        page: null,
        menu: null
      }
    }
  },
  mounted() {
    this.globalSearch('page', this.search, this.page, this.limit);
  },
  methods: {

    changePage(entity, page, limit) {
      this.globalSearch(entity, this.search, page, limit)
    },

    globalSearch(entity, search, page, limit) {
      this.loading[entity] = true;
      axios.get(this.urls.searchPage + '/' + entity + '/' + page + '/' + limit + '/' + search, {})
          .then((response) => {
            if (response.data.result.total > 0) {
              this.results[entity] = response.data.result;
              this.paginate[entity] = response.data.paginate;
              this.total += response.data.result.total;
            }

          }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.loading[entity] = false;
      });
    }
  }

}
</script>

<template>

  <div class="float-end mt-2" v-if="this.total !== 0">{{ this.total }} {{ this.translate.totalResult }}
    <b>{{ this.search }}</b></div>
  <div class="float-end mt-2" v-else> {{ this.translate.totalNoResult }} <b>{{ this.search }}</b></div>
  <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
    <li class="nav-item">
      <button class="nav-link active position-relative" id="search-page-tab" data-bs-toggle="pill" data-bs-target="#search-page" type="button" role="tab" aria-controls="search-page" aria-selected="true">
        <i class="bi bi-file-earmark-text-fill"></i> {{ this.translate.ongletPage.onglet }}
        <div v-if="this.loading.page" class="spinner-border spinner-border-sm" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>

        <span v-if="!this.loading.page && this.results.page !== null" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
          {{ this.results.page.total }}
          <span class="visually-hidden">unread messages</span>
        </span>
      </button>
    </li>
    <li class="nav-item">
      <button class="nav-link" id="search-menu-tab" data-bs-toggle="pill" data-bs-target="#search-menu" type="button" role="tab" aria-controls="search-menu" aria-selected="false">
        <i class="bi bi-menu-button-wide-fill"></i> {{ this.translate.ongletMenu.onglet }}
        <div v-if="this.loading.menu" class="spinner-border spinner-border-sm" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>

        <span v-if="!this.loading.menu && this.results.menu !== null" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
          {{ this.results.menu.total }}
          <span class="visually-hidden">unread messages</span>
        </span>
      </button>
    </li>
    <li class="nav-item">
      <button class="nav-link" id="search-tag-tab" data-bs-toggle="pill" data-bs-target="#search-tag" type="button" role="tab" aria-controls="search-tag" aria-selected="false">Contact</button>
    </li>
    <li class="nav-item">
      <button class="nav-link" id="search-faq-tab" data-bs-toggle="pill" data-bs-target="#search-faq" type="button" role="tab" aria-controls="search-faq" aria-selected="false">aaa</button>
    </li>
    <li class="nav-item">
      <button class="nav-link" id="search-user-tab" data-bs-toggle="pill" data-bs-target="#search-user" type="button" role="tab" aria-controls="search-user" aria-selected="false">bbb</button>
    </li>
  </ul>

  <div class="tab-content" id="pills-tabContent">
    <div class="tab-pane fade show active" id="search-page" role="tabpanel" :class="this.loading.page === true ? 'block-grid' : ''">
        <div v-if="this.loading.page" class="position-absolute top-50 start-50 translate-middle">
          <div class="spinner-border text-primary" role="status"></div>
          <span class="txt-overlay">{{ translate.ongletPage.loading }}</span>
        </div>

      <h5 v-if="this.results.page !== null">{{ this.results.page.total }} {{ this.translate.ongletPage.title }}</h5>
      <p>{{ this.translate.ongletPage.description }}</p>

      <div v-if="this.results.page === null && !this.loading.page">
        {{ this.translate.ongletPage.noResult }}

      </div>
      <div v-if="this.results.page !== null">
        <tab-page-search-result
            :result="this.results.page"
            :translate="this.translate"
            :paginate="this.paginate.page"
            :entity="'page'"
            @change-page-event="this.changePage"
        >
        </tab-page-search-result>
      </div>
    </div>
    <div class="tab-pane fade" id="search-menu" role="tabpanel" :class="this.loading.menu === true ? 'block-grid' : ''">
      <div v-if="this.loading.menu" class="position-absolute top-50 start-50 translate-middle">
        <div class="spinner-border text-primary" role="status"></div>
        <span class="txt-overlay">{{ translate.ongletMenu.loading }}</span>
      </div>

      <h5 v-if="this.results.menu !== null">{{ this.results.menu.total }} {{ this.translate.ongletMenu.title }}</h5>
      <p>{{ this.translate.ongletMenu.description }}</p>

      <div v-if="this.results.menu === null && !this.loading.menu">
        {{ this.translate.ongletMenu.noResult }}

      </div>

    </div>


    <div class="tab-pane fade" id="search-tag" role="tabpanel">tag</div>
    <div class="tab-pane fade" id="search-faq" role="tabpanel">faq</div>
    <div class="tab-pane fade" id="search-user" role="tabpanel">user</div>
  </div>
</template>