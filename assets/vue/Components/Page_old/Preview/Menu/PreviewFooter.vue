<script>
/**
 * Permet d'afficher la préview du footer
 * @author Gourdon Aymeric
 * @version 1.0
 */

export default {
  name: 'PreviewFooter',
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
     * Retourne l'année courante
     * @return {number}
     */
    getCurrentYear() {
      return new Date().getFullYear();
    },

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
     * Génère le footer de type Col avec profondeur
     * @param elem
     * @param html
     */
    renderColDeep(elem, html) {
      elem.forEach((element) => {
        html +=
          ' <li class="nav-item mb-2"><a class="nav-link p-0 text-body-secondary"  target="' +
          element.target +
          '" href="' +
          this.getUrl(element) +
          '">' +
          element.label +
          '</a>';
        if (this.isHaveChildren(element)) {
          html += '<ul>';
          html = this.renderColDeep(element.elements, html);
          html += '</ul>';
        }
        html += '</li>';
      });
      return html;
    },
  },
};
</script>

<template>
  <!-- Menu footer 1 ligne à droite TYPE_FOOTER_1_ROW_RIGHT !-->
  <div v-if="this.pMenu.type === 17">
    <footer class="d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top">
      <p class="col-md-4 mb-0 text-body-secondary">
        © <span v-html="this.getCurrentYear()"></span> {{ this.data.name }}
      </p>

      <a
        :href="this.data.url"
        class="col-md-4 d-flex align-items-center justify-content-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none"
      >
        <i class="bi" :class="this.data.logo"></i>&nbsp;{{ this.data.name }}
      </a>

      <ul class="nav col-md-4 justify-content-end">
        <li v-for="element in this.pMenu.elements" class="nav-item" :set="url = this.getUrl(element)">
          <a class="nav-link px-2 text-body-secondary" :href="url" :target="element.target"> {{ element.label }}</a>
        </li>
      </ul>
    </footer>
  </div>

  <!-- Menu footer 1 ligne centrée TYPE_FOOTER_1_ROW_CENTER !-->
  <div v-if="this.pMenu.type === 18">
    <footer class="py-3 my-4">
      <ul class="nav justify-content-center border-bottom pb-3 mb-3">
        <li v-for="element in this.pMenu.elements" class="nav-item" :set="url = this.getUrl(element)">
          <a class="nav-link px-2 text-body-secondary" :href="url" :target="element.target"> {{ element.label }}</a>
        </li>
      </ul>
      <p class="text-center text-body-secondary">
        © <span v-html="this.getCurrentYear()"></span> {{ this.data.name }}
      </p>
    </footer>
  </div>

  <!-- Menu Footer TYPE_FOOTER_COLONNES !-->
  <div v-if="this.pMenu.type === 16">
    <footer class="row pt-5 mt-5 border-top">
      <div class="col">
        <a :href="this.data.url" class="d-flex align-items-center mb-3 link-body-emphasis text-decoration-none">
          <i class="bi" :class="this.data.logo"></i>&nbsp;{{ this.data.name }}
        </a>
        <p class="text-body-secondary">© <span v-html="this.getCurrentYear()"></span></p>
      </div>

      <div class="col"></div>

      <div v-for="element in this.pMenu.elements" class="col-1">
        <div v-if="this.isHaveChildren(element)">
          <span>{{ element.label }}</span>
          <ul class="nav flex-column" v-html="this.renderColDeep(element.elements, '')"></ul>
        </div>
        <a v-else :href="url" :target="element.target" class="text-decoration-none"> {{ element.label }}</a>
      </div>
    </footer>
  </div>
</template>
