<script>/**
 * Permet d'afficher la preview des menus left et right
 * @author Gourdon Aymeric
 * @version 1.0
 */


export default {
  name: 'PreviewMenuLeftRight',
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

    /**
     * Génère les profondeurs de deepMenu
     * @param elem
     * @param html
     * @return string
     */
    renderCollapse(elem, html) {

      elem.forEach((element) => {

        let id = this.getRandomInt(100000);

        if (!this.isHaveChildren(element)) {
          html += '<li><a class="link-body-emphasis d-inline-flex text-decoration-none rounded element-hover"  target="' + element.target + '" href="' + this.getUrl(element) + '">' + element.label + '</a>'
        } else {
          html += '<li><a href="#" class="link-body-emphasis d-inline-flex text-decoration-none rounded link-toggle element-hover no-control" data-bs-toggle="collapse" data-bs-target="#demo-' + id + '" aria-expanded="false"> ' + element.label + '</a>'
          html += '<div class="collapse" id="demo-' + id + '">';
          html += '<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">'
          html = this.renderCollapse(element.elements, html);
          html += '</ul></div>';
        }
        html += '</li>';

      });

      return html;
    },

  }

}
</script>

<template>
  <div id="global-menu" class="flex-shrink-0 p-3 bg-body-tertiary border border-1 border-light">
    <a :href="this.data.url" class="d-flex align-items-center pb-3 mb-3 link-body-emphasis text-decoration-none border-bottom">
      <i class="bi pe-none me-2 h4 mt-2" :class="this.data.logo"></i>
      <span class="fs-5 fw-semibold">{{ this.data.name }}</span>
    </a>

    <ul v-if="this.pMenu.type === 11" class="list-unstyled ps-0">
      <li v-for="element in this.pMenu.elements" class="mb1">
        <a class="btn d-inline-flex align-items-center rounded border-0 collapsed element-hover"
            :target="element.target" :href="this.getUrl(element)">{{ element.label }}
        </a>
      </li>
    </ul>

    <ul v-if="this.pMenu.type === 12" class="list-unstyled ps-0">
      <li v-for="element in this.pMenu.elements" class="mb-1" :set="id = this.getRandomInt(100000)">
        <a v-if="!this.isHaveChildren(element)" class="btn d-inline-flex align-items-center rounded border-0 collapsed element-hover"
            :target="element.target" :href="this.getUrl(element)">{{ element.label }}
        </a>
        <button v-else class="btn btn-toggle d-inline-flex align-items-center rounded border-0 collapsed" data-bs-toggle="collapse" :data-bs-target="'#demo-' + id" aria-expanded="false">
          {{ element.label }}
        </button>
        <div v-if="this.isHaveChildren(element)" :id="'demo-' + id" class="collapse">
          <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small" v-html="this.renderCollapse(element.elements, '')"></ul>
        </div>
      </li>
    </ul>
  </div>
</template>