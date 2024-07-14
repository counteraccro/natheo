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
    locale: String,
    idSelect: Number
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
    },

    haveParent() {
      return this.menuElement.hasOwnProperty('parent');
    },

    /**
     * Si l'élément est sélectionné
     * @returns {string}
     */
    isSelected() {
      if (this.menuElement.id === this.idSelect) {
        return "selected";
      } else {
        return '';
      }
    }


  },

  methods: {

    /**
     * Edit un élément au menu
     */
    updateElement() {
      emitter.emit('update-menu-element', this.menuElement.id);
    },

    /**
     * Créer un nouvel élément au menu
     */
    newElement() {
      emitter.emit('new-menu-element', this.menuElement.id);
    },

    /**
     * Ouvre ou ferme un noeud
     */
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
    <div class="li-hover" :class="this.isSelected">
    <span class="no-control" @click="this.toggle">
      <i v-if="this.haveParent" class="bi bi-arrow-return-right"></i>
      <i v-else class="bi bi-arrow-right-square"></i>
      {{ this.getTranslationValueByKeyAndByLocale(this.menuElement.menuElementTranslations, 'textLink') }}
      <span v-if="this.haveChildren">
        <i class="bi" :class="this.isOpen ? 'bi-chevron-down' : 'bi-chevron-right'"></i>
      </span>
    </span>
      <span class="float-end">
      <i class="bi bi-pencil-fill" @click="this.updateElement"></i>&nbsp;
      <i class="bi bi-x-lg" @click="this.updateElement"></i>
    </span>
    </div>
    <ul class="tree-menu" v-show="this.isOpen" v-if="this.haveChildren">
      <menu-tree
          v-for="menuElement in this.menuElement.children.menuElements"
          :menu-element="menuElement"
          :locale="this.locale"
          :id-select="this.idSelect"
          :update-element="this.updateElement"
          :new="this.newElement"
      >
      </menu-tree>
      <li>
        <div @click="this.newElement">
          <i class="bi bi-plus-square"></i>
          Nouveau
        </div>
      </li>
    </ul>
  </li>

</template>