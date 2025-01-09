<script>/**
 * Permet d'afficher la préview du footer
 * @author Gourdon Aymeric
 * @version 1.0
 */
import SearchPaginate from "./SearchPaginate.vue";


export default {
  name: 'TabPageSearchResult',
  components: {SearchPaginate},
  props: {
    result: Object,
    paginate: Object,
    translate: Object,
    entity: String
  },
  emits: ['change-page-event'],
  data() {
    return {}
  },
  mounted() {
    console.log(this.result)
  },
  methods: {
    changePageEvent(page, limit)
    {
      this.$emit('change-page-event', this.entity, page, limit)
    }
  }

}
</script>

<template>
  <div class="card mb-3" v-for="row in result.elements">
    <div class="row">
      <div class="col-3">
        <svg class="bd-placeholder-img rounded-start" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: Image" preserveAspectRatio="xMidYMid slice" focusable="false"><title>Placeholder</title><rect width="100%" height="100%" fill="#868e96"></rect><text x="50%" y="50%" fill="#dee2e6" dy=".3em">Image</text></svg>
      </div>
      <div class="col">
        <div class="card-body">
          <h5 class="card-title" v-html="row.label">
          </h5>
          <p class="m-0"><b>Dans le contenu :</b></p>
          <p v-if="row.contents[0]" class="card-text m-0" v-for="content in row.contents">
            <i class="bi bi-arrow-return-right"></i> <span v-html="content"></span>
          </p>
          <p v-else class="card-text mt-0">{{ this.translate.ongletPage.noResultContent }}</p>

          <div class="float-end">
            <a v-for="(url, key) in row.urls" :href="url" target="_blank" class="btn btn-secondary btn-sm ms-1">
              <i v-if="key==='edit'" class="bi bi-pencil-fill"></i>
              <i v-if="key==='preview'" class="bi bi-box-arrow-up-right"></i>
            </a>
          </div>
          <p class="card-text mt-2"><small class="text-body-secondary">
            {{ this.translate.ongletPage.create }} {{ row.date.create }},
            {{ this.translate.ongletPage.update }} {{ row.date.update }}
           {{ this.translate.ongletPage.author }}  <span v-html="row.author"></span>
          </small></p>
        </div>
      </div>
    </div>
  </div>

  <search-paginate
      :nb-elements="parseInt(this.paginate.limit)"
      :current-page="this.paginate.current"
      :nb-elements-total="this.result.total"
      :translate="this.translate.paginate"
      @change-page-event="changePageEvent"
  >

  </search-paginate>
</template>