<script>
/**
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
    return {};
  },
  methods: {
    /**
     *
     * @param element
     * @return {*|string}
     */
    getUrl(element) {
      if (element.slug === '') {
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

    /**
     * Génère les dropdown
     * @param elem
     * @param html
     * @returns {*}
     */
    renderDeepDropDown(elem, html) {
      elem.forEach((element) => {
        if (!this.isHaveChildren(element)) {
          html +=
            '<li><a class="dropdown-item" target="' +
            element.target +
            '" href="' +
            this.getUrl(element) +
            '">' +
            element.label +
            '</a></li>';
        } else {
          html +=
            '<li class="dropdown dropend">' +
            '<a class="dropdown-item dropdown-toggle no-control" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' +
            element.label +
            '</a>\n' +
            '<ul class="dropdown-menu">';
          html = this.renderDeepDropDown(element.elements, html);
          html += '</ul>' + '</li>';
        }
      });
      return html;
    },

    /**
     * Génère le rendu des bigs menus
     * @param elem
     * @param html
     */
    renderBigMenu(elem, html) {
      let column = 0;
      let row = 0;
      let col = this.getColNumberByType();
      elem.forEach((element, key) => {
        if (column !== element.columnPosition) {
          column = element.columnPosition;
          row = element.rowPosition;
          html += '<div class="' + col + '"><ul class="list-unstyled">';
        }

        if (row !== element.rowPosition) {
          row = element.rowPosition;
          html += '<li><hr class="dropdown-divider"></li>';
        }

        html +=
          '<li><a class="dropdown-item"  target="' +
          element.target +
          '" href="' +
          this.getUrl(element) +
          '">' +
          element.label +
          '</a>';
        if (this.isHaveChildren(element)) {
          html += '<ul class="list-custom">';
          html = this.renderBigMenuDeep(element.elements, html);
          html += '</ul>';
        }
        html += '</li>';

        if (elem[key + 1] === undefined || elem[key + 1].columnPosition !== column) {
          html += '</ul></div>';
        }
      });
      return html;
    },

    /**
     * Génère les profondeurs de deepMenu
     * @param elem
     * @param html
     * @return string
     */
    renderBigMenuDeep(elem, html) {
      elem.forEach((element) => {
        html +=
          '<li><a class="dropdown-item"  target="' +
          element.target +
          '" href="' +
          this.getUrl(element) +
          '">' +
          element.label +
          '</a>';
        if (this.isHaveChildren(element)) {
          html += '<ul class="list-custom">';
          html = this.renderBigMenuDeep(element.elements, html);
          html += '</ul>';
        }
        html += '</li>';
      });

      return html;
    },

    /**
     * Génère le bon nombre de colonnes en fonction du type
     * @return {string}
     */
    getColNumberByType() {
      let col = '';
      switch (this.pMenu.type) {
        case 3: // TYPE_HEADER_MENU_DEROULANT_BIG_MENU
          col = 'col';
          break;
        case 4: // TYPE_HEADER_MENU_DEROULANT_BIG_MENU_2_COLONNES
          col = 'col-6';
          break;
        case 5: // TYPE_HEADER_MENU_DEROULANT_BIG_MENU_3_COLONNES
          col = 'col-4';
          break;
        case 6: // TYPE_HEADER_MENU_DEROULANT_BIG_MENU_4_COLONNES
          col = 'col-3';
          break;
        default:
          col = 'col';
      }
      return col;
    },
  },
};
</script>

<template>
  <nav class="navbar navbar-expand-lg bg-body-tertiary" id="navbar-header-demo">
    <div class="container-fluid">
      <a class="navbar-brand" :href="this.data.url"><i class="bi" :class="this.data.logo"></i> {{ this.data.name }}</a>
      <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarNav"
        aria-controls="navbarNav"
        aria-expanded="false"
        aria-label="Toggle navigation"
      >
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
          <li
            v-for="element in this.pMenu.elements"
            class="nav-item"
            :class="this.isHaveChildren(element) === true ? 'dropdown' : ''"
          >
            <a
              v-if="!this.isHaveChildren(element)"
              class="nav-link"
              :target="element.target"
              :href="this.getUrl(element)"
              >{{ element.label }}</a
            >
            <a
              v-else
              class="nav-link dropdown-toggle no-control"
              href="#"
              role="button"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              {{ element.label }}
            </a>
            <ul
              v-if="this.isHaveChildren(element)"
              class="dropdown-menu"
              v-html="this.renderDeepDropDown(element.elements, '')"
            ></ul>
          </li>
        </ul>

        <!-- TYPE_HEADER_MENU_DEROULANT_BIG_MENU -->
        <ul class="navbar-nav" v-if="this.pMenu.type > 2">
          <li
            v-for="element in this.pMenu.elements"
            class="nav-item"
            :class="this.isHaveChildren(element) === true ? 'dropdown has-megamenu' : ''"
          >
            <a
              v-if="!this.isHaveChildren(element)"
              class="nav-link"
              :target="element.target"
              :href="this.getUrl(element)"
              >{{ element.label }}</a
            >
            <a
              v-else-if="!element.disabled"
              class="nav-link dropdown-toggle no-control"
              href="#"
              role="button"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              {{ element.label }}
            </a>
            <div v-if="this.isHaveChildren(element)" class="dropdown-menu megamenu" role="menu">
              <div class="row" v-html="this.renderBigMenu(element.elements, '')"></div>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>
