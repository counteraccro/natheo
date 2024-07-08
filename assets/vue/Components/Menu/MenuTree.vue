<script>/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Composant pour gérer l'arbre du menu
 */
import {emitter} from "../../../utils/useEvent";

export default {
  name: "MenuTree",
  components: {},
  emit: ['update-element', 'new-element'],
  props: {
    menuElement: Object,
    locale: String
  },
  data() {
    return {
      isOpen: false
    }
  },
  mounted() {

  },

  computed: {
    haveChildren() {
      return this.menuElement.hasOwnProperty('children') && this.menuElement.children.menuElements.length
    }
  },

  methods: {

    /**
     * Edit un élément au menu
     */
    updateElement()
    {
      emitter.emit('update-menu-element', this.menuElement.id);
    },

    /**
     * Créer un nouvel élément au menu
     */
    newElement()
    {
      emitter.emit('new-menu-element', this.menuElement.id);
    },

    toggle() {
      if (this.haveChildren) {
        this.isOpen = !this.isOpen
      }
    },

    /**
     * Retourne le lien avec la traduction en fonction de la locale
     * @param tabMenuElementTranslation
     * @param key
     */
    getTranslationValueByKeyAndByLocale(tabMenuElementTranslation, key) {

      let str = '';
      tabMenuElementTranslation.forEach((menuElementTranslation) => {
        if (menuElementTranslation.locale === this.locale) {
          console.log(menuElementTranslation[key]);
          str = menuElementTranslation[key];
        }
      })
      return str;
    },
  }
}
</script>

<template>

  <li>
    <div class="icon-link icon-link-hover no-control"
        @click="this.toggle"
    >
      {{ this.getTranslationValueByKeyAndByLocale(this.menuElement.menuElementTranslations, 'textLink') }}
      <span v-if="this.haveChildren"><i class="bi" :class="this.isOpen ? 'bi-chevron-down' : 'bi-chevron-right'"></i></span>
    </div>
    <div class="btn btn-sm"
        @click="this.updateElement"
    >
      Edit
    </div>
    <ul v-show="this.isOpen" v-if="this.haveChildren">
      <menu-tree
          v-for="menuElement in this.menuElement.children.menuElements"
          :menu-element="menuElement"
          :locale="this.locale"
          :update-element="this.updateElement"
          :new="this.newElement"
          >
      </menu-tree>
      <li @click="this.newElement">Nouveau</li>
    </ul>
  </li>

</template>