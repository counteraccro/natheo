<script>/**
 * Permet d'afficher la préview du footer
 * @author Gourdon Aymeric
 * @version 1.0
 */


export default {
  name: 'TabResultSearch',
  props: {
    result: Object,
    translate: Object
  },
  emits: [],
  data() {
    return {}
  },
  methods: {

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
          <p v-else class="card-text mt-0">Aucun résultat n'a été trouvé dans le contenu de la page...</p>

          <div class="float-end">
            <button class="btn btn-secondary btn-sm"><i class="bi bi-pencil-fill"></i></button>
          </div>

          <p class="card-text"><small class="text-body-secondary">Last updated 3 mins ago</small></p>
        </div>
      </div>
    </div>
  </div>
</template>