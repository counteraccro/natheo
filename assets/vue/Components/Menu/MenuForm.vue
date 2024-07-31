<script>/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Composant pour éditer / créer un menu
 */

export default {
  name: "MenuForm",
  components: {},
  emit: ['reorder-element', 'change-parent'],
  props: {
    menuElement: Object,
    translate: Object,
    locale: String,
    pages: Object,
    positions: Object,
    allElements: Object,
  },
  watch: {
    menuElement: 'entryPoint',
    locale: ['createListePage', 'createListeAllElement']
  },
  data() {
    return {
      titleForm: '',
      selectPage: '',
      searchPage: '',
      listPages: [],
      listColumn: [],
      listRow: [],
      listParents: [],
      modeLink: 'interne',
      oldColumnPosition: '',
      oldRowPosition: '',
      labelDisabled: '',
      infoDisabled: '',
    }
  },
  mounted() {
    this.entryPoint();
  },
  computed: {
    events() {
      return events
    },

    /**
     * Filtre sur tables
     * @returns {ObjectConstructor}
     */
    filteredPage() {

      const searchPage = this.searchPage && this.searchPage.toLowerCase()
      let data = this.listPages;
      if (searchPage) {
        data = data.filter((row) => {
          return Object.keys(row).some((key) => {
            return String(row.title).toLowerCase().indexOf(searchPage) > -1
          })
        })
      }
      return data;
    }
  },
  methods: {

    /**
     * Point d'entrée
     */
    entryPoint() {

      this.renderDisabledLabel();
      this.renderTitle();
      this.createListeAllElement();
      this.createListePage();
      this.createListeColumn();
      this.createListRow(this.menuElement.columnPosition);
      this.oldColumnPosition = this.menuElement.columnPosition;
      this.oldRowPosition = this.menuElement.rowPosition;

      this.modeLink = 'externe';
      this.selectPage = '';
      if (this.menuElement.page !== '') {
        this.modeLink = 'interne';
        this.selectPage = this.menuElement.page;
      }
    },

    /**
     * Génère la liste de parent en fonction de la locale
     */
    createListeAllElement() {
      this.listParents = [];
      Object.entries(this.allElements).forEach((data) => {
          this.listParents.push({value: data[0], label: data[1][this.locale]});
      })
    },

    /**
     * Gère le rendu des labels pour le disabled
     */
    renderDisabledLabel() {

      this.labelDisabled = this.translate.radio_label_disabled_element;
      this.infoDisabled = this.translate.text_info_disabled_element;
      if (!this.menuElement.disabled) {
        this.labelDisabled = this.translate.radio_label_enabled_element;
        this.infoDisabled = this.translate.text_info_enabled_element;
      }
    },

    /**
     * Génère la liste de column
     */
    createListeColumn() {
      this.listColumn = [];
      for (let i = 1; i <= (this.positions.columnMax); i++) {
        this.listColumn.push({value: i, label: i});
      }
    },

    /**
     * Génère la liste de row en fonction de column
     * @param column
     */
    createListRow(column) {
      this.listRow = [];
      for (let i = 1; i <= (this.positions[column]['rowMax']); i++) {
        this.listRow.push({value: i, label: i});
      }
    },

    /**
     * Mise à jour de la liste de row
     * @param event
     */
    updateListRow(event) {
      this.createListRow(event.target.value)
    },

    /**
     * Construit la liste de page en fonction de la locale
     */
    createListePage() {
      this.listPages = [];
      this.searchPage = '';
      for (const property in this.pages) {
        this.listPages.push({title: this.pages[property][this.locale]['title'], id: property});
      }
    },

    /**
     * Prépare les données pour les renvoyer à Menu.vue pour générer le trie
     */
    reorderElements() {
      let data = {
        'newColumn': this.menuElement.columnPosition,
        'oldColumn': this.oldColumnPosition,
        'newRow': this.menuElement.rowPosition,
        'oldRow': this.oldRowPosition,
        'id': this.menuElement.id,
        'parent': 0
      };

      if (this.menuElement.hasOwnProperty('parent')) {
        data.parent = this.menuElement.parent;
      }

      this.$emit('reorder-element', data);
    },

    /**
     * Met à jour l'url en fonction de la page
     * @param event
     */
    updateUrlByPage(event) {
      let tmp = this.getDataPageById(event.target.value);
      let tabTmp = this.menuElement.menuElementTranslations;
      tabTmp.forEach((element) => {
        element.link = tmp[element.locale]['url']
      })

      this.menuElement.menuElementTranslations = tabTmp;
    },

    /**
     * Retourne les données d'une page en fonction de son id
     * @param id
     * @returns {*[]}
     */
    getDataPageById(id) {
      let tmp = [];
      for (const property in this.pages) {
        if (property === id) {
          tmp = this.pages[property];
        }
      }
      return tmp;
    },

    /**
     * Permet de changer de parent
     */
    switchParent(event) {

      let parent = event.target.value;
      if (parent === "") {
        parent = 0;
      }

      this.$emit('change-parent', this.menuElement.id, parent);
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
    <div class="card-header text-bg-secondary">{{ this.titleForm }}</div>
    <div class="card-body">

      <div class="row">

        <div class="col">
          <label for="url-interne" class="form-label">{{ this.translate.url_type_label }}</label>
          <div class="clearfix"></div>
          <div class="mb-3 mt-2">
            <div class="form-check form-check-inline">
              <input class="form-check-input" v-model="this.modeLink" type="radio" name="modeLink" id="url-interne" value="interne">
              <label class="form-check-label" for="url-interne"> {{ this.translate.radio_label_url_interne }}</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" v-model="this.modeLink" type="radio" name="modeLink" id="url-externe" value="externe">
              <label class="form-check-label" for="url-externe">{{ this.translate.radio_label_url_externe }}</label>
            </div>
          </div>

          <div class="mt-1 mb-3">
          <label for="liste-target" class="form-label">{{ this.translate.element_link_target_label }}</label>
          <select class="form-select" id="liste-target" v-model="this.menuElement.linkTarget">
            <option value="_self">{{ this.translate.element_link_target_label_self }}</option>
            <option value="_blank">{{ this.translate.element_link_target_label_blank }}</option>
          </select>
          </div>
        </div>
        <div class="col">
          <label for="liste-parent" class="form-label">{{ this.translate.parent_label }}</label>
          <select class="form-select" id="liste-parent" v-model="this.menuElement.parent" @change="this.switchParent">
            <option value="">{{ this.translate.parent_empty }}</option>
            <option v-for="parent in this.listParents" :value="parent.value">{{ parent.label }}</option>
          </select>
        </div>
      </div>

      <fieldset class="mb-3">
        <legend>{{ this.translate.title_position }}</legend>
        <div class="row">
          <div class="col">
            <label for="liste-column-position" class="form-label">{{ this.translate.position_column_label }}</label>
            <select class="form-select" id="liste-column-position" v-model="this.menuElement.columnPosition" @change="(event) => {this.updateListRow(event); this.reorderElements()}">
              <option v-for="column in this.listColumn" :value="column.value">{{ column.label }}</option>

            </select>
          </div>
          <div class="col">
            <label for="liste-column-row" class="form-label">{{ this.translate.position_row_label }}</label>
            <select class="form-select" id="liste-column-row" v-model="this.menuElement.rowPosition" @change="this.reorderElements">
              <option v-for="row in this.listRow" :value="row.value">{{ row.label }}</option>
            </select>
          </div>
        </div>
      </fieldset>

      <fieldset v-if="this.modeLink === 'interne'" class="mb-3">
        <legend>{{ this.translate.input_search_page }}</legend>
        <div class="input-group">
          <input type="text" class="form-control" id="search-page" v-model="this.searchPage" :placeholder="this.translate.input_search_page_placeholder">
          <select class="form-select" id="id-list-page" size="1" style="width: 40%" v-model="this.selectPage" @change="updateUrlByPage">
            <option v-for="page in this.filteredPage" :value="page.id">
              {{ page.title }}
            </option>
          </select>
        </div>
      </fieldset>

      <div class="row">
        <div v-for="meElTranslation in this.menuElement.menuElementTranslations" class="col-sm">

          <fieldset class="mb-1" :class="meElTranslation.locale === locale ? 'border border-primary' : ''">
            <legend v-if="meElTranslation.locale === locale" class="text-primary">
              <b>{{ this.translate['block_' + meElTranslation.locale] }}</b>
            </legend>
            <legend v-else>
              {{ this.translate['block_' + meElTranslation.locale] }}
            </legend>

            <div class="mb-3">
              <label :for="'text-link-' + meElTranslation.id" class="form-label">{{ this.translate.input_link_text }}</label>
              <input type="text" class="form-control" :id="'text-link-' + meElTranslation.id" placeholder="name@example.com" v-model="meElTranslation.textLink">
            </div>
            <div v-if="this.modeLink === 'interne'" class="mb-3">
              <label :for="'url-link-interne' + meElTranslation.id" class="form-label">{{ this.translate.input_link_url }}</label>
              <input type="text" disabled class="form-control" :id="'url-link-interne' + meElTranslation.id" v-model="meElTranslation.link">
            </div>
            <div v-else class="mb-3">
              <label :for="'url-link-externe' + meElTranslation.id" class="form-label">{{ this.translate.input_link_external_url }}</label>
              <input type="text" class="form-control" :id="'url-link-externe' + meElTranslation.id" v-model="meElTranslation.externalLink">
            </div>
          </fieldset>
        </div>
      </div>

      <div class="mb-3 mt-4">
        <div class="form-check form-check-inline">
          <input class="form-check-input" v-model="this.menuElement.disabled" type="radio" name="elementDisabled" id="el-enabled" :value="false" @change="this.renderDisabledLabel">
          <label class="form-check-label" for="el-enabled"> {{ this.translate.radio_label_enabled_element }}</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" v-model="this.menuElement.disabled" type="radio" name="elementDisabled" id="el-disabled" :value="true" @change="this.renderDisabledLabel">
          <label class="form-check-label" for="el-disabled">{{ this.translate.radio_label_disabled_element }}</label>
        </div>
      </div>

      <div class="float-end">
        <i> {{ this.infoDisabled }} </i>
      </div>

    </div>
  </div>

</template>