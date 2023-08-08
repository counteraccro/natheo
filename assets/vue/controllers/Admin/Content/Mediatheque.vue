<script>
/**
 * Interface de la médiathèque
 */

import MediasGrid from "../../../Components/Mediatheque/MediasGrid.vue";
import MediasBreadcrumb from "../../../Components/Mediatheque/MediasBreadcrumb.vue";
import axios from "axios";
import {Modal} from "bootstrap";
import {isEmpty} from "lodash-es";

export default {
  name: "Mediatheque",
  components: {
    MediasGrid,
    MediasBreadcrumb
  },
  props: {
    url: String,
    translate: Object
  },
  data() {
    return {
      loading: false,
      modalFolder: '',
      medias: [],
      currentFolder: [],
      filter: 'created_at',
      filterIcon: 'bi-clock',
      order: 'asc',
      orderIcon: 'bi-sort-down',
      render: 'grid',
      folderId: 0,
      folderEdit: [],
      folderName: '',
      folderError: '',
      folderCanSubmit: false,
      urlActions: '',
    }
  },

  mounted() {
    this.modalFolder = new Modal(document.getElementById("modal-folder"), {});
    this.loadMedia();
  },

  methods: {
    isEmpty,
    /**
     * Charge les médias
     */
    loadMedia() {

      this.loading = true;

      axios.post(this.url, {
        'folder': this.folderId,
        'order': this.order,
        'filter': this.filter
      }).then((response) => {
        this.medias = response.data.medias;
        this.currentFolder = response.data.currentFolder;
        this.urlActions = response.data.url;
      }).catch((error) => {
        console.log(error);
      }).finally(() => {
        this.loading = false
      });
    },

    /**
     * Change l'ordre du tri
     * @param order
     */
    changeOrder(order) {

      this.order = order;
      if (order === 'asc') {
        this.orderIcon = 'bi-sort-down';
      } else {
        this.orderIcon = 'bi-sort-up';
      }

      this.loadMedia();
    },

    changeFilter(filter) {
      this.filter = filter;
      switch (filter) {
        case "created_at":
          this.filterIcon = 'bi-clock'
          break;
        case "name":
          this.filterIcon = 'bi-card-text'
          break;
        case "type":
          this.filterIcon = 'bi-file'
          break;
      }

      this.loadMedia();
    },

    /**
     * Charge les données du dossier en id
     * @param id
     */
    loadDataInFolder(id) {
      this.folderId = id;
      this.loadMedia();
    },

    /**
     * Permet de switcher le mode d'affichage des médias
     * @param render
     */
    switchRender(render) {
      this.render = render;
    },

    /**
     * Retourne le path du dossier courant
     * @returns {string}
     */
    getPathCurrentFolder() {
      return this.currentFolder.path;
    },

    /**
     * Retourne la taille du dossier
     * @returns {*|string}
     */
    getSizeCurrentFolder() {
      return this.currentFolder.size;
    },

    /** Bloc méthode gestion des dossiers **/

    /**
     * edition d'un dossier
     * @param id
     */
    editFolder(id) {
      this.loading = true;
      axios.post(this.urlActions.loadFolder, {
        'id': id,
        'action': 'edit'
      }).then((response) => {
        this.folderEdit = response.data.folder;
        this.folderName = this.folderEdit.name;
      }).catch((error) => {
        console.log(error);
      }).finally(() => {
        this.loading = false;
        this.openModalFolder();
      });
    },

    /**
     * Ouvre la modale pour la gestion des dossiers
     */
    openModalFolder() {
      this.modalFolder.show();
    },

    /**
     * Ferme la modale pour la gestion des dssiers
     */
    closeModalFolder() {
      this.folderEdit = [];
      this.folderName = '';
      this.folderCanSubmit = false;
      let element = document.getElementById('input-folder-name');
      element.classList.remove('is-invalid');
      this.modalFolder.hide();
    },

    /**
     * Génère le titre de la modale
     */
    getTitleModalFolder() {
      if (isEmpty(this.folderEdit)) {
        return this.translate.folder.new;
      } else {
        return this.translate.folder.edit + ' ' + this.folderEdit.name;
      }
    },

    /**
     * Vérifie si le nom est correcte ou non
     */
    validateFolderName()
    {
      let error = false;
      let defaultName = '';
      if (!isEmpty(this.folderEdit)) {
        defaultName = this.folderEdit.name;
      }


      let regex = /^[a-zA-Z0-9]{3,15}$/;
      if (!regex.test(this.folderName)) {
        error = true;
        this.folderError = this.translate.folder.input_error;
      }

      if(this.folderName === defaultName)
      {
        error = true;
      }

      this.renderErrorFolderInput(error);
    },

    /**
     * Affichage l'erreur du formulaire pour l'édition / création d'un dossier
     * @param boolean (true erreur / false masquer l'erreur)
     */
    renderErrorFolderInput(boolean)
    {
      let element = document.getElementById('input-folder-name');
      if(boolean)
      {
        element.classList.add('is-invalid');
        this.folderCanSubmit = false;
      }
      else {
        element.classList.remove('is-invalid');
        this.folderCanSubmit = true;
        this.folderError = '';
      }
    },

    /**
     * Soumission du formulaire pour éditer / new folder
     */
    submitFolder()
    {

      let editFolderId = 0;
      if (!isEmpty(this.folderEdit)) {
        editFolderId = this.folderEdit.id
      }

      this.loading = true;
      axios.post(this.urlActions.saveFolder, {
        'name': this.folderName,
        'currentFolder': this.currentFolder.id,
        'editFolder' : editFolderId
      }).then((response) => {
        if(response.data.result === 'error')
        {
            this.folderError = response.data.msg;
            this.renderErrorFolderInput(true);
        }
        else {
          this.renderErrorFolderInput(false);
        }

      }).catch((error) => {
        console.log(error);
      }).finally(() => {
        this.loading = false;

      });
    }

    /** fin bloc gestion des dossiers **/
  }
}

</script>


<template>

  <div id="global-mediatheque" :class="this.loading === true ? 'block-grid' : ''">
    <div v-if="this.loading" class="overlay">
      <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1000;">
        <div class="spinner-border text-primary" role="status"></div>
        <span class="txt-overlay">{{ this.translate.loading }}</span>
      </div>
    </div>

    <div class="card border border-secondary">
      <div class="card-header text-bg-secondary">
        <div class="float-end">
          {{ this.getSizeCurrentFolder() }}
        </div>
        <MediasBreadcrumb
            :paths="this.currentFolder.root"
            @load-folder="this.loadDataInFolder"
        >
        </MediasBreadcrumb>
      </div>
      <div class="card-body">
        <div>
          <div class="btn btn-secondary me-1" @click="this.openModalFolder()">
            <i class="bi bi-folder-plus"></i>
            <span class="d-none-mini">&nbsp;{{ this.translate.btn_new_folder }}</span>
          </div>
          <div class="btn btn-secondary me-1">
            <i class="bi bi-file-plus"></i>
            <span class="d-none-mini">&nbsp;{{ this.translate.btn_new_media }}</span>
          </div>
          <div class="float-end">
            <div class="btn-group">
              <button type="button" class="btn btn-secondary dropdown-toggle me-1" data-bs-toggle="dropdown" aria-expanded="false">
                {{ this.translate.btn_filtre }} <i class="bi" :class="this.filterIcon"></i>
              </button>
              <ul class="dropdown-menu">
                <li>
                  <a class="dropdown-item" href="#" @click="this.changeFilter('created_at')"><i class="bi bi-clock"></i> {{ this.translate.filtre_date }}</a>
                </li>
                <li>
                  <a class="dropdown-item" href="#" @click="this.changeFilter('name')"><i class="bi bi-card-text"></i> {{ this.translate.filtre_nom }}</a>
                </li>
                <li>
                  <a class="dropdown-item" href="#" @click="this.changeFilter('type')"><i class="bi bi-file"></i> {{ this.translate.filtre_type }}</a>
                </li>
              </ul>
            </div>
            <div class="btn-group">
              <button type="button" class="btn btn-secondary dropdown-toggle me-1" data-bs-toggle="dropdown" aria-expanded="false">
                {{ this.translate.btn_order }} <i class="bi" :class="this.orderIcon"></i>
              </button>
              <ul class="dropdown-menu">
                <li>
                  <a class="dropdown-item" href="#" @click="this.changeOrder('asc')"><i class="bi bi-sort-down"></i> {{ this.translate.order_asc }}</a>
                </li>
                <li>
                  <a class="dropdown-item" href="#" @click="this.changeOrder('desc')"><i class="bi bi-sort-up"></i> {{ this.translate.order_desc }}</a>
                </li>
              </ul>
            </div>
            <input type="radio" class="btn-check no-control" name="options-render" id="btn-grid" autocomplete="off" checked @change="this.switchRender('grid')">
            <label class="btn me-1 btn-secondary" for="btn-grid"><i class="bi bi-grid"></i></label>

            <input type="radio" class="btn-check no-control" name="options-render" id="btn-list" autocomplete="off" @change="this.switchRender('list')">
            <label class="btn btn-secondary" for="btn-list"><i class="bi bi-list"></i></label>
          </div>
        </div>


        <div v-if="render === 'grid'">
          <medias-grid
              :medias="this.medias"
              :translate="this.translate.media"
              @load-data-folder="this.loadDataInFolder"
              @edit-folder="this.editFolder"
          >
          </medias-grid>
        </div>

        <div v-else>
          list render
        </div>

      </div>
    </div>
  </div>


  <!-- Modal pour la gestion des dossier -->
  <div class="modal fade" id="modal-folder" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-secondary">
          <h1 class="modal-title fs-5 text-white">
            <i class="bi" :class="isEmpty(this.folderEdit)?'bi-folder-plus': 'bi-pencil-fill'"></i> {{ this.getTitleModalFolder() }}
          </h1>
          <button type="button" class="btn-close" @click="this.closeModalFolder()"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="folderName" class="form-label">{{ this.translate.folder.input_label }} *</label>
            <input type="text" v-model="folderName"
                @keyup="this.validateFolderName()"
                class="form-control"
                id="input-folder-name"
                :placeholder="this.translate.folder.input_label_placeholder">
            <div class="invalid-feedback">
              {{ this.folderError }}
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <div class="btn btn-dark" @click="this.closeModalFolder()">{{ this.translate.folder.btn_cancel }}</div>
          <div v-if="isEmpty(this.folderEdit)" @click="this.submitFolder()" class="btn btn-primary" :class="this.folderCanSubmit ? '':'disabled'">
            {{ this.translate.folder.btn_submit_create }}
          </div>
          <div v-else class="btn btn-primary" @click="this.submitFolder()" :class="this.folderCanSubmit ? '':'disabled'">
            {{ this.translate.folder.btn_submit_edit }}
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Fin Modal pour la gestion des dossier -->


</template>

<style scoped>

</style>