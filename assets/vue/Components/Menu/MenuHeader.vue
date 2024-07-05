<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Composant header pour la création /edition d'un menu
 */

export default {
  name: "MenuHeader",
  components: {},
  emit: [],
  props: {
    menu: Object,
    type: Number,
    data: Object,
    locale: String,
  },
  watch: {
    type: 'switchType',
    locale: 'switchType'
  },
  data() {
    return {
      demoMenu: [],
    }
  },
  mounted() {

  },
  methods: {

    /**
     * Permet de déterminer le type de rendu
     */
    switchType() {
      this.demoMenu = [];
      switch (this.type) {
        case 1 :
          console.log('TYPE_HEADER_SIDE_BAR');
          this.generateHeaderSiteBar();
          break;
        case 2 :
          console.log('TYPE_HEADER_MENU_DEROULANT');
          break;
        case 3 :
          console.log('TYPE_HEADER_MENU_DEROULANT_1_LIGNE_1_COLONNE');
          break;
        case 4 :
          console.log('TYPE_HEADER_MENU_DEROULANT_1_LIGNE_2_COLONNES');
          break;
        case 5 :
          console.log('TYPE_HEADER_MENU_DEROULANT_1_LIGNE_3_COLONNES');
          break;
        case 6 :
          console.log('TYPE_HEADER_MENU_DEROULANT_1_LIGNE_4_COLONNES');
          break;
        case 7 :
          console.log('TYPE_HEADER_MENU_DEROULANT_2_LIGNES_1_COLONNE');
          break;
        case 8 :
          console.log('TYPE_HEADER_MENU_DEROULANT_2_LIGNES_2_COLONNES');
          break;
        case 9 :
          console.log('TYPE_HEADER_MENU_DEROULANT_2_LIGNES_3_COLONNES');
          break;
        case 10 :
          console.log('TYPE_HEADER_MENU_DEROULANT_2_LIGNES_4_COLONNES');
          break;
      }

      console.log(this.demoMenu);
    },

    generateHeaderSiteBar()
    {
        this.menu.menuElements.forEach((menuElement) => {
            let element = [];
            menuElement.menuElementTranslations.forEach((menuElementTranslation) => {
              if(menuElementTranslation.locale === this.locale) {
                console.log(menuElementTranslation.textLink);
                element['link'] = menuElementTranslation.textLink;
              }
          })

          this.demoMenu.push(element);
        })
    }
  }
}
</script>

<template>

  <nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Navbar</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="#">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">Features</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">Pricing</a>
          </li>
          <li class="nav-item">
            <a class="nav-link disabled" aria-disabled="true">Disabled</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

</template>