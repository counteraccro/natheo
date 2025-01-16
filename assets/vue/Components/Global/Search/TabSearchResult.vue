<script>/**
 * Permet d'afficher la préview du footer
 * @author Gourdon Aymeric
 * @version 1.0
 */
import SearchPaginate from "./SearchPaginate.vue";


export default {
  name: 'TabSearchResult',
  components: {SearchPaginate},
  props: {
    result: Object,
    paginate: Object,
    translate: Object,
    translatePaginate: Object,
    entity: String
  },
  emits: ['change-page-event'],
  data() {
    return {}
  },
  mounted() {
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
      <div class="col-1">
        <div class="bg-opacity-75 bg-light h-100 w-100"></div>
      </div>
      <div class="col">
        <div class="card-body">
          <h5 class="card-title" v-html="row.label">
          </h5>

          <p v-if="row.contents[0]" class="m-0"><b>{{ this.translate.content }}</b></p>
          <p v-if="row.contents[0]" class="card-text m-0" v-for="content in row.contents">
            <i class="bi bi-arrow-return-right"></i> <span v-html="content"></span>
          </p>
          <p v-else class="card-text mt-0">{{ this.translate.noResultContent }}</p>

          <div class="float-end">
            <a v-for="(url, key) in row.urls" :href="url" target="_blank" class="btn btn-secondary btn-sm ms-1">
              <i v-if="key==='edit'" class="bi bi-pencil-fill"></i>
              <i v-if="key==='preview'" class="bi bi-box-arrow-up-right"></i>
            </a>
          </div>
          <p class="card-text mt-2"><small class="text-body-secondary">
            {{ this.translate.create }} {{ row.date.create }},
            {{ this.translate.update }} {{ row.date.update }}
           <span v-if="row.author"> {{ this.translate.author }}  <span v-html="row.author"></span></span>
          </small></p>
        </div>
      </div>
    </div>
  </div>

  <search-paginate
      :nb-elements="this.paginate.limit"
      :current-page="this.paginate.current"
      :nb-elements-total="this.result.total"
      :translate="this.translatePaginate"
      @change-page-event="changePageEvent"
  >

  </search-paginate>
</template>