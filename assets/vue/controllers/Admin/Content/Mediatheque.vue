<script>
/**
 * Interface de la médiathèque
 */

import MediasGrid from "../../../Components/Mediatheque/MediasGrid.vue";
import MediasBreadcrumb from "../../../Components/Mediatheque/MediasBreadcrumb.vue";
import MediaModalFolder from "../../../Components/Mediatheque/MediaModalFolder.vue";
import axios from "axios";
import {Modal} from "bootstrap";

export default {
  name: "Mediatheque",
  components: {
    MediaModalFolder,
    MediasGrid,
    MediasBreadcrumb
  },
  props: {
    url: String,
    translate: []
  },
  data() {
    return {
      loading: false,
      modal: '',
      modalContent: '',
      medias: [],
      currentFolder: [],
      filter: 'created_at',
      filterIcon: 'bi-clock',
      order: 'asc',
      orderIcon: 'bi-sort-down',
      render: 'grid',
      folderId: 0,
      folderEditId: 0,
      folderEdit: [],
      urlActions: '',
    }
  },

  mounted() {
    this.modal = new Modal(document.getElementById("modal-mediatheque"), {});
    this.loadMedia();
  },

  methods: {

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

    openModal(content)
    {
      this.modalContent = content;
      this.modal.show();
    },

    closeModal()
    {
      this.modal.hide();
    },

    newFolder() {
      this.folderEdit = [];
      this.openModal('folder');
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
     * edition d'un dossier
     * @param id
     */
    editFolder(id)
    {
      this.loading = true;
      axios.post(this.urlActions.loadFolder, {
        'id': id,
        'action' : 'edit'
      }).then((response) => {
        this.folderEdit = response.data.folder;
      }).catch((error) => {
        console.log(error);
      }).finally(() => {
        this.loading = false;
        this.openModal('folder');
      });
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
    }
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
          <div class="btn btn-secondary me-1" @click="this.newFolder()">
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

  <div class="modal fade" id="modal-mediatheque" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">

        <media-modal-folder v-if="this.modalContent='folder'"
            :current-folder-id="this.currentFolder.id"
            :folder-edit="this.folderEdit"
            :translate="this.translate.folder"
            @hide-modal-folder="this.closeModal()"
        ></media-modal-folder>
      </div>
    </div>
  </div>



</template>

<style scoped>

</style>