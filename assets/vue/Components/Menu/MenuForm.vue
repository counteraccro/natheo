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
    translate: Object,
    locale: String
  },
  watch: {
    menuElement: 'entryPoint'
  },
  data() {
    return {
      titleForm: ''
    }
  },
  mounted() {
    this.entryPoint();
  },
  methods: {

    /**
     * Point d'entrée
     */
    entryPoint() {
      this.orderElementTranslation();
      this.renderTitle();
    },

    orderElementTranslation() {
      let tmp, tmpIndex = '';
      this.menuElement.menuElementTranslations.forEach((element, index) => {

        if (element.locale === this.locale) {
          tmp = element;
          tmpIndex = index;
        }
      })
      this.menuElement.menuElementTranslations.splice(tmpIndex, 1);
      this.menuElement.menuElementTranslations.unshift(tmp);

    },

    /**
     * Affiche le titre du formulaire
     */
    renderTitle() {

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

        <fieldset :class="meElTranslation.locale === locale ? 'border border-primary' : ''">
          <legend v-if="meElTranslation.locale === locale" class="text-primary">
            <b>data {{ meElTranslation.locale }}</b>
          </legend>
          <legend v-else>
            data {{ meElTranslation.locale }}
          </legend>
          <div class="mb-3">
            <label for="exampleFormControlInput1" class="form-label">toto</label>
            <input type="text" class="form-control" id="exampleFormControlInput1" placeholder="name@example.com" v-model="meElTranslation.textLink">
          </div>
          <div class="mb-3">
            <label for="exampleFormControlInput1" class="form-label">toto</label>
            <input type="text" class="form-control" id="exampleFormControlInput1" placeholder="name@example.com" v-model="meElTranslation.link">
          </div>
        </fieldset>

      </div>

      <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
      <a href="#" class="btn btn-primary">Go somewhere</a>
    </div>
  </div>

</template>