<script>/**
 * Permet de faire une recherche globale dans le CMS
 * @author Gourdon Aymeric
 * @version 1.0
 */
import axios from "axios";
import TabSearchResult from "../../../Components/Global/Search/TabSearchResult.vue";


export default {
  name: 'GlobalSearch',
  components: {TabSearchResult},
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
        user: null
      },
      paginate: {
        page: null,
        menu: null,
        faq: null,
        tag: null,
        user: null,
      }
    }
  },
  mounted() {
    this.globalSearch('page', this.search, this.page, this.limit, false);
    this.globalSearch('menu', this.search, this.page, this.limit, false);
    this.globalSearch('faq', this.search, this.page, this.limit, false);
  },
  methods: {

    changePage(entity, page, limit) {
      this.globalSearch(entity, this.search, page, limit, true)
    },

    globalSearch(entity, search, page, limit, reload) {
      this.loading[entity] = true;
      axios.get(this.urls.searchPage + '/' + entity + '/' + page + '/' + limit + '/' + search, {})
          .then((response) => {
            if (response.data.result.total > 0) {
              this.results[entity] = response.data.result;
              this.paginate[entity] = response.data.paginate;
              if (!reload) {
                this.total += response.data.result.total;
              }
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
      <button class="nav-link active" id="search-page-tab" data-bs-toggle="pill" data-bs-target="#search-page" type="button" role="tab" aria-controls="search-page" aria-selected="true">
        <i class="bi bi-file-earmark-text-fill"></i> {{ this.translate.ongletPage.onglet }}
        <div v-if="this.loading.page" class="spinner-border spinner-border-sm" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>

        <span v-if="!this.loading.page && this.results.page !== null" class="ml-5 badge rounded-pill bg-danger">
          {{ this.results.page.total }}
        </span>
      </button>
    </li>
    <li class="nav-item">
      <button class="nav-link" id="search-menu-tab" data-bs-toggle="pill" data-bs-target="#search-menu" type="button" role="tab" aria-controls="search-menu" aria-selected="false">
        <i class="bi bi-menu-button-wide-fill"></i> {{ this.translate.ongletMenu.onglet }}
        <div v-if="this.loading.menu" class="spinner-border spinner-border-sm" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>

        <span v-if="!this.loading.menu && this.results.menu !== null" class="ml-5 badge rounded-pill bg-danger">
          {{ this.results.menu.total }}
        </span>
      </button>
    </li>
    <li class="nav-item">
      <button class="nav-link" id="search-faq-tab" data-bs-toggle="pill" data-bs-target="#search-faq" type="button" role="tab" aria-controls="search-faq" aria-selected="false">
        <i class="bi bi-question-circle-fill"></i> {{ this.translate.ongletFaq.onglet }}
        <div v-if="this.loading.faq" class="spinner-border spinner-border-sm" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>

        <span v-if="!this.loading.faq && this.results.faq !== null" class="ml-5 badge rounded-pill bg-danger">
          {{ this.results.faq.total }}
        </span>
      </button>
    </li>
    <li class="nav-item">
      <button class="nav-link" id="search-tag-tab" data-bs-toggle="pill" data-bs-target="#search-tag" type="button" role="tab" aria-controls="search-tag" aria-selected="false">
        <i class="bi bi-tags-fill"></i> {{ this.translate.ongletTag.onglet }}
        <div v-if="this.loading.tag" class="spinner-border spinner-border-sm" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>

        <span v-if="!this.loading.tag && this.results.tag !== null" class="ml-5 badge rounded-pill bg-danger">
          {{ this.results.tag.total }}
        </span>
      </button>
    </li>
    <li class="nav-item">
      <button class="nav-link" id="search-user-tab" data-bs-toggle="pill" data-bs-target="#search-user" type="button" role="tab" aria-controls="search-user" aria-selected="false">
        <i class="bi bi-person-fill"></i> {{ this.translate.ongletUser.onglet }}
        <div v-if="this.loading.user" class="spinner-border spinner-border-sm" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>

        <span v-if="!this.loading.user && this.results.user !== null" class="ml-5 badge rounded-pill bg-danger">
          {{ this.results.user.total }}
        </span>

      </button>
    </li>
  </ul>

  <div class="tab-content" id="pills-tabContent">
    <div class="tab-pane fade show active" id="search-page" role="tabpanel">
      <div :class="this.loading.page === true ? 'block-grid' : ''">
        <div v-if="this.loading.page" class="mt-3 float-end">
          <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
          <span class="txt-overlay p-2">{{ translate.ongletPage.loading }}</span>
        </div>

        <h5 v-if="this.results.page !== null">{{ this.results.page.total }} {{ this.translate.ongletPage.title }}</h5>
        <h5 v-else> 0 {{ this.translate.ongletPage.title }}</h5>
        <p>{{ this.translate.ongletPage.description }}</p>

        <div v-if="this.results.page === null && !this.loading.page">
          {{ this.translate.ongletPage.noResult }}

        </div>
        <div v-if="this.results.page !== null">
          <tab-search-result key="1"
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
    <div class="tab-pane fade" id="search-menu" role="tabpanel">
      <div :class="this.loading.menu === true ? 'block-grid' : ''">
        <div v-if="this.loading.menu" class="mt-3 float-end">
          <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
          <span class="txt-overlay p-2"> {{ translate.ongletMenu.loading }}</span>
        </div>

        <h5 v-if="this.results.menu !== null">{{ this.results.menu.total }} {{ this.translate.ongletMenu.title }}</h5>
        <h5 v-else> 0 {{ this.translate.ongletMenu.title }}</h5>
        <p>{{ this.translate.ongletMenu.description }}</p>

        <div v-if="this.results.menu === null && !this.loading.menu">
          {{ this.translate.ongletMenu.noResult }}
        </div>
        <div v-if="this.results.menu !== null">
          <tab-search-result key="2"
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


    <div class="tab-pane fade" id="search-faq" role="tabpanel">

      <div :class="this.loading.faq === true ? 'block-grid' : ''">
        <div v-if="this.loading.faq" class="mt-3 float-end">
          <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
          <span class="txt-overlay p-2"> {{ translate.ongletFaq.loading }}</span>
        </div>

        <h5 v-if="this.results.faq !== null">{{ this.results.faq.total }} {{ this.translate.ongletFaq.title }}</h5>
        <h5 v-else> 0 {{ this.translate.ongletFaq.title }}</h5>
        <p>{{ this.translate.ongletFaq.description }}</p>

        <div v-if="this.results.faq === null && !this.loading.faq">
          {{ this.translate.ongletFaq.noResult }}
        </div>
        <div v-if="this.results.faq !== null">
          <tab-search-result key="2"
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
    <div class="tab-pane fade" id="search-tag" role="tabpanel">
      <div :class="this.loading.tag === true ? 'block-grid' : ''">
        <div v-if="this.loading.tag" class="mt-3 float-end">
          <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
          <span class="txt-overlay p-2"> {{ translate.ongletTag.loading }}</span>
        </div>

        <h5 v-if="this.results.tag !== null">{{ this.results.tag.total }} {{ this.translate.ongletTag.title }}</h5>
        <h5 v-else> 0 {{ this.translate.ongletTag.title }}</h5>
        <p>{{ this.translate.ongletTag.description }}</p>

        <div v-if="this.results.tag === null && !this.loading.tag">
          {{ this.translate.ongletTag.noResult }}
        </div>
      </div>
    </div>
    <div class="tab-pane fade" id="search-user" role="tabpanel">
      <div :class="this.loading.user === true ? 'block-grid' : ''">
        <div v-if="this.loading.user" class="mt-3 float-end">
          <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
          <span class="txt-overlay p-2"> {{ translate.ongletUser.loading }}</span>
        </div>

        <h5 v-if="this.results.tag !== null">{{ this.results.tag.total }} {{ this.translate.ongletUser.title }}</h5>
        <h5 v-else> 0 {{ this.translate.ongletUser.title }}</h5>
        <p>{{ this.translate.ongletUser.description }}</p>

        <div v-if="this.results.tag === null && !this.loading.tag">
          {{ this.translate.ongletTag.noResult }}
        </div>
      </div>
    </div>
  </div>
</template>