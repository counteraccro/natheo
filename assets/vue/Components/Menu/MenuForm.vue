<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Composant pour éditer / créer un menu
 */

export default {
  name: "MenuForm",
  components: {},
  emit: [],
  props: {
    menuElement: Object,
    translate: Object
  },
  watch: {
    menuElement: 'updateTitle'
  },
  data() {
    return {
      titleForm: ''
    }
  },
  mounted() {
    this.updateTitle();
  },
  methods: {
    updateTitle() {

      console.log(this.menuElement);

      if (this.menuElement.id !== null) {
        this.titleForm = this.translate.title_edit + ' #' + this.menuElement.id
      } else {
        this.titleForm = this.translate.title_new
      }
    }
  }
}
</script>

<template>

  <div class="card border border-secondary">
    <h5 class="card-header text-bg-secondary">{{ this.titleForm }}</h5>
    <div class="card-body">

      <div v-for="meElTranslation in this.menuElement.menuElementTranslations">
        <div class="mb-3">
          <label for="exampleFormControlInput1" class="form-label">toto</label>
          <input type="text" class="form-control" id="exampleFormControlInput1" placeholder="name@example.com" v-model="meElTranslation.textLink">
        </div>
        <div class="mb-3">
          <label for="exampleFormControlInput1" class="form-label">toto</label>
          <input type="text" class="form-control" id="exampleFormControlInput1" placeholder="name@example.com" v-model="meElTranslation.link">
        </div>
      </div>

      <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
      <a href="#" class="btn btn-primary">Go somewhere</a>
    </div>
  </div>

</template>