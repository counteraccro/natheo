<script>/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Composant pour gérer l'arbre du menu
 */
import {emitter} from "../../../utils/useEvent";

export default {
  name: "MenuTree",
  components: {},
  emit: [],
  props: {
    translate: Object,
    menuElement: Object,
    locale: String,
    idSelect: Number,
    deep: Number
  },
  data() {
    return {
      isOpen: false
    }
  },
  mounted() {},
  updated() {
    this.forceOpen();
  },

  computed: {

    /**
     * Test si des enfants existent
     * @returns {boolean}
     */
    haveChildren() {
      return this.menuElement.hasOwnProperty('children') && this.menuElement.children.length
    },

    /**
     * Test si l'élément possède un parent
     * @returns {boolean}
     */
    haveParent() {
      return this.menuElement.hasOwnProperty('parent') && this.menuElement.parent !== "";
    },

    /**
     * Test si le menuElement est désactivé ou non
     * @returns {boolean}
     */
    isDisabled()
    {
      return this.menuElement.disabled;
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
     * Force l'ouverture d'un noeud dans le cas d'une création
     */
    forceOpen() {
      if (this.menuElement.hasOwnProperty('children')) {
        Object.entries(this.menuElement.children).forEach((value) => {
          let obj = value[1];
          if (obj.id === this.idSelect) {
            this.isOpen = true;
          }
        });
      }
    },

    /**
     * Edit un élément au menu
     */
    updateElement() {
      console.log(this.deep);
      emitter.emit('update-menu-element', {id : this.menuElement.id, deep : this.deep});
    },

    /**
     * Créer un nouvel élément au menu
     */
    newElement() {
      emitter.emit('new-menu-element', {id : this.menuElement.id, deep : this.deep + 1});
    },

    /**
     * CRéer un nouvel élément enfant autre que celui par défaut
     * @param id
     */
    newChildren(id) {
      emitter.emit('new-menu-element', {id : this.menuElement.id, deep : this.deep +1});
    },

    /**
     * supprime un nouvel élément
     */
    deleteElement() {
      emitter.emit('delete-menu-element', this.menuElement.id);
    },

    /**
     * Ouvre ou ferme un nœud
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
      &nbsp;<i v-if="this.isDisabled" class="bi bi-eye-slash-fill"></i>
      {{ this.getTranslationValueByKeyAndByLocale(this.menuElement.menuElementTranslations, 'textLink') }}
      <span v-if="this.haveChildren">
        <i class="bi" :class="this.isOpen ? 'bi-chevron-down' : 'bi-chevron-right'"></i>
      </span>
    </span>
      <span class="float-end">
        <i v-if="!this.haveChildren" class="bi bi-plus-square" @click="this.newElement"></i>&nbsp;
        <i class="bi bi-pencil-fill" @click="this.updateElement"></i>&nbsp;
        <i class="bi bi-x-lg" @click="this.deleteElement"></i>
    </span>
    </div>
    <ul class="tree-menu" v-show="this.isOpen" v-if="this.haveChildren">
      <menu-tree
          v-for="menuElement in this.menuElement.children"
          :menu-element="menuElement"
          :translate="this.translate"
          :locale="this.locale"
          :id-select="this.idSelect"
          :deep="(this.deep+1)"
      >
      </menu-tree>
      <li>
        <div>
          <span class="btn btn-outline-secondary btn-sm" @click="this.newElement"><i class="bi bi-plus-square"></i>
            {{ this.translate.btn_new_menu_element }}
          </span>
        </div>
      </li>
    </ul>
  </li>

</template>