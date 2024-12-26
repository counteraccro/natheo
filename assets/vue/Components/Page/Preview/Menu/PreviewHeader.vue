<script>/**
 * Permet d'afficher la preview des menus left et right
 * @author Gourdon Aymeric
 * @version 1.0
 */


export default {
  name: 'PreviewHeader',
  props: {
    pMenu: Object,
    data: Object,
  },
  emits: [],
  data() {
    return {}
  },
  methods: {
    /**
     *
     * @param element
     * @return {*|string}
     */
    getUrl(element) {
      if (element.slug === "") {
        return element.url;
      }
      return this.data.url + '/' + element.slug;
    },

    /**
     *  Détermine si l'élément possède des enfants ou non
     * @param element
     */
    isHaveChildren(element) {
      return element.hasOwnProperty('elements');
    },

    /**
     * Génère un nombre aléatoire
     * @param max
     * @return {number}
     */
    getRandomInt(max) {
      return Math.floor(Math.random() * max);
    },

  }

}
</script>

<template>
  <nav class="navbar navbar-expand-lg bg-body-tertiary" id="navbar-header-demo">
    <div class="container-fluid">
      <a class="navbar-brand" :href="this.data.url"><i class="bi" :class="this.data.logo"></i> {{ this.data.name }}</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <!-- TYPE_HEADER_SIDE_BAR -->
        <ul class="navbar-nav" v-if="this.pMenu.type === 1">
          <li v-for="element in this.pMenu.elements" class="nav-item">
            <a class="nav-link no-control" :target="element.target" :href="this.getUrl(element)">{{ element.label }}</a>
          </li>
        </ul>

        <!-- TYPE_HEADER_MENU_DEROULANT -->
        <ul class="navbar-nav" v-if="this.pMenu.type === 2">
          <li>TYPE_HEADER_MENU_DEROULANT</li>
        </ul>

        <!-- TYPE_HEADER_MENU_DEROULANT_BIG_MENU -->
        <ul class="navbar-nav" v-if="this.pMenu.type > 2">
          <li>TYPE_HEADER_MENU_DEROULANT_BIG_MENU</li>
        </ul>
      </div>
    </div>
  </nav>
</template>