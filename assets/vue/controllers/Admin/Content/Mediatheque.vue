<script>
/**
 * Interface de la médiathèque
 */

import MediasGrid from "../../../Components/Mediatheque/MediasGrid.vue";
import MediasBreadcrumb from "../../../Components/Mediatheque/MediasBreadcrumb.vue";
import MediaModalInfo from "../../../Components/Mediatheque/MediaModalInfo.vue";
import FileUpload from "../../../Components/FileUpload.vue";
import MediaMove from "../../../Components/Mediatheque/MediaMove.vue";
import axios from "axios";
import {Modal} from "bootstrap";
import {isEmpty} from "lodash-es";

export default {
  name: "Mediatheque",
  components: {
    MediasGrid,
    MediasBreadcrumb,
    MediaModalInfo,
    FileUpload,
    MediaMove
  },
  props: {
    url: String,
    translate: Object
  },
  data() {
    return {
      loading: false,
      loadingUploadMsg: '',
      modalFolder: '',
      modalInfo: '',
      modalUpload: '',
      modalEditMedia: '',
      modalMove: '',
      modalTrash: '',
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
      folderSuccess: '',
      folderCanSubmit: false,
      mediaEdit: {
        id: 0,
        name: '',
        description: '',
        thumbnail: '',
        status: '',
      },
      infoData: [],
      extAccept: 'csv,pdf,jpg,png,xls,xlsx,doc,docx,gif',
      urlActions: '',
      dataMove: [],
      mediaMoveStatus: '',
      canDelete: false,
      nbTrash: 0,
      trash: {
        type: '',
        id: '',
      },
      trashMsg: '',
      trashConfirm: false,
      mediasTrash: [],
    }
  },

  mounted() {
    this.modalFolder = new Modal(document.getElementById("modal-folder"), {});
    this.modalInfo = new Modal(document.getElementById("modal-info"), {});
    this.modalUpload = new Modal(document.getElementById("modal-upload"), {});
    this.modalMove = new Modal(document.getElementById("modal-move"), {});
    this.modalEditMedia = new Modal(document.getElementById("modal-edit-media"), {});
    this.modalTrash = new Modal(document.getElementById("modal-trash"), {});
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
        this.canDelete = response.data.canDelete;
      }).catch((error) => {
        console.log(error);
      }).finally(() => {
        this.getNbTrash();
        this.loading = false
      });
    },

    /**
     * Retourne le nombre d'éléments dans la corbeille
     */
    getNbTrash() {
      axios.post(this.urlActions.nbTrash, {}).then((response) => {
        this.nbTrash = response.data.nb;
      }).catch((error) => {
        console.log(error);
      }).finally(() => {
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
     * Ferme la modale pour la gestion des dossiers
     */
    closeModalFolder() {
      this.folderEdit = [];
      this.folderName = '';
      this.folderSuccess = '';
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
    validateFolderName() {
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

      if (this.folderName === defaultName) {
        error = true;
      }

      this.renderErrorFolderInput(error);
    },

    /**
     * Affichage l'erreur du formulaire pour l'édition / création d'un dossier
     * @param boolean (true erreur / false masquer l'erreur)
     */
    renderErrorFolderInput(boolean) {
      let element = document.getElementById('input-folder-name');
      if (boolean) {
        element.classList.add('is-invalid');
        this.folderCanSubmit = false;
      } else {
        element.classList.remove('is-invalid');
        this.folderCanSubmit = true;
        this.folderError = '';
      }
    },

    /**
     * Soumission du formulaire pour éditer / new folder
     */
    submitFolder() {

      let editFolderId = 0;
      this.folderSuccess = this.translate.folder.msg_wait_create;
      if (!isEmpty(this.folderEdit)) {
        editFolderId = this.folderEdit.id
        this.folderSuccess = this.translate.folder.msg_wait_edit;
      }

      axios.post(this.urlActions.saveFolder, {
        'name': this.folderName,
        'currentFolder': this.currentFolder.id,
        'editFolder': editFolderId
      }).then((response) => {
        if (response.data.result === 'error') {
          this.folderSuccess = '';
          this.folderError = response.data.msg;
          this.renderErrorFolderInput(true);
        } else {
          this.folderSuccess = response.data.msg;
          this.renderErrorFolderInput(false);
          setTimeout(this.closeModalFolder, 3000);
          setTimeout(this.loadMedia, 3000);
        }
      }).catch((error) => {
        console.log(error);
      }).finally(() => {
      });
    },
    /** fin bloc gestion des dossiers **/

    /** Bloc modal information **/

    /**
     * Charge les informations de la popin information
     * @param type
     * @param id
     */
    loadDataInformation(type, id) {
      this.loading = true;
      axios.post(this.urlActions.loadInfo, {
        'id': id,
        'type': type,
      }).then((response) => {
        this.infoData = response.data
      }).catch((error) => {
        console.log(error);
      }).finally(() => {
        this.loading = false;
        this.openModalInfo();
      });
    },

    /**
     * Ouvre la modale pour les informations
     */
    openModalInfo() {
      this.modalInfo.show();
    },

    /**
     * Ferme la modale pour les informations
     */
    closeModalInfo() {
      this.infoMedia = [];
      this.infoFolder = [];
      this.modalInfo.hide();
    },

    /** Fin bloc modal information **/

    /** bloc modal upload **/

    /**
     * Ouvre la modale pour l'upload
     */
    openModalUpload() {
      this.modalUpload.show();
    },

    /**
     * Ferme la modale pour l'upload
     */
    closeModalUpload() {
      this.modalUpload.hide();
      this.loadingUploadMsg = '';
    },

    /**
     * Télécharge un fichier sur le serveur
     * @param file
     */
    getUploadedData(file) {

      this.loadingUploadMsg = this.translate.upload.loading_msg

      axios.post(this.urlActions.upload, {
        'file': file,
        'folder': this.folderId,
      }).then((response) => {
        this.loadingUploadMsg = this.translate.upload.loading_msg_success
        setTimeout(this.closeModalUpload, 3000);
        setTimeout(this.loadMedia, 2500);
      }).catch((error) => {
        console.log(error);
      }).finally(() => {
      });
    },

    /** Fin bloc modal upload **/

    /** bloc modal edit média **/

    /**
     * Ouvre la modale pour l'édition d'un média
     */
    openModalEditMedia() {
      this.modalEditMedia.show();
    },

    /**
     * Ferme la modale pour l'édition d'un média
     */
    closeModalEditMedia() {
      this.modalEditMedia.hide();
      this.mediaEdit = {
        id: 0,
        name: '',
        description: '',
        thumbnail: '',
        status: '',
      }
    },

    /**
     * Charge un media en fonction d'un id
     * @param id
     */
    editMedia(id) {
      this.loading = true;
      axios.post(this.urlActions.loadMediaEdit, {
        'id': id,
      }).then((response) => {
        this.mediaEdit = {
          id: response.data.media.id,
          name: response.data.media.name,
          description: response.data.media.description,
          thumbnail: response.data.media.thumbnail,
          status: '',
        }
      }).catch((error) => {
        console.log(error);
      }).finally(() => {
        this.loading = false;
        this.openModalEditMedia();
      });
    },

    /**
     * Sauvegarde les modifications d'un média
     */
    saveMedia() {
      this.mediaEdit.status = "loading";
      axios.post(this.urlActions.saveMediaEdit, {
        'media': this.mediaEdit,
      }).then((response) => {
        this.mediaEdit.status = "success";
        setTimeout(this.closeModalEditMedia, 3000);
        setTimeout(this.loadMedia, 2500);
      }).catch((error) => {
        console.log(error);
      }).finally(() => {
      });
    },

    /** fin bloc modal edit média **/

    /** bloc modal move **/

    /**
     * Ouvre la modale pour le move
     */
    openModalMove() {
      this.modalMove.show();
    },

    /**
     * Ferme la modale le move
     */
    closeModalMove() {
      this.modalMove.hide();
      this.dataMove = [];
      this.mediaMoveStatus = '';
    },

    /**
     * Charge une liste de dossier pour le déplacement
     * @param id
     * @param type
     */
    loadListFolderMove(type, id) {
      this.loading = true;
      axios.post(this.urlActions.listeMove, {
        'id': id,
        'type': type
      }).then((response) => {
        this.dataMove = response.data.dataMove;
      }).catch((error) => {
        console.log(error);
      }).finally(() => {
        this.loading = false;
        this.openModalMove();
      });
    },

    /**
     * Déplace un média
     * @param idToMove
     * @param id
     * @param type
     */
    moveMedia(idToMove, id, type) {
      this.mediaMoveStatus = 'loading'
      axios.post(this.urlActions.move, {
        'idToMove': idToMove,
        'id': id,
        'type': type
      }).then((response) => {

      }).catch((error) => {
        console.log(error);
      }).finally(() => {
        this.mediaMoveStatus = "success";
        setTimeout(this.closeModalMove, 3000);
        setTimeout(this.loadMedia, 2500);
      });
    },

    /** fin bloc modal move **/

    /** bloc trash **/

    /**
     * Ouvre la modale de confirmation de mise à la corbeille
     * @param type
     * @param id
     * @param nameM
     * @param confirm
     */
    confirmTrash(type, id, nameM, confirm) {
      this.trash.type = type;
      this.trash.id = id;
      this.trashConfirm = confirm;

      if (!confirm) {
        if (type === 'folder') {
          this.trashMsg = this.translate.trash.text_folder + ' : <b>' + nameM +
              '</b> ?<br /><span class="text-warning"><i class="bi bi-exclamation-triangle-fill"></i> <i>' + this.translate.trash.text_info + '</i></span>';
        } else {
          this.trashMsg = this.translate.trash.text_media + ' : <b>' + nameM + '</b> ?'
        }
        this.openModalTrash();
      } else {
        this.trashMsg = '<div class="spinner-border text-primary" role="status"></div> '
            + this.translate.trash.loading;
        this.updateTrash();
      }

    },

    /**
     * Ouvre la modale pour la corbeille
     */
    openModalTrash() {
      this.modalTrash.show();
    },

    /**
     * Ferme la modale pour la corbeille
     */
    closeModalTrash() {
      this.modalTrash.hide();
      this.trash = {
        type: '',
        id: ''
      }
      this.trashMsg = '';
      this.trashConfirm = false;
    },

    /**
     * Met à jour le média avec la valeur de la corbeille
     */
    updateTrash() {
      axios.post(this.urlActions.updateTrash, {
        'trash': true,
        'id': this.trash.id,
        'type': this.trash.type
      }).then((response) => {

      }).catch((error) => {
        console.log(error);
      }).finally(() => {
        this.trashMsg = '<span class="text-success"><i class="bi bi-check"></i>' + this.translate.trash.success_trash + '</span>';
        setTimeout(this.closeModalTrash, 3000);
        setTimeout(this.loadMedia, 2500);
      });
    },

    /**
     * Charge le contenu de la corbeille
     */
    loadInTrash() {
      this.loading = true;
      axios.post(this.urlActions.listTrash, {
      }).then((response) => {
          this.mediasTrash = response.data.mediasTrash
      }).catch((error) => {
        console.log(error);
      }).finally(() => {
        this.loading = false;
      });
    }

    /** fin bloc trash **/

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
      <div v-if="render !== 'trash'" class="card-header text-bg-secondary">
        <div class="float-end">
          {{ this.getSizeCurrentFolder() }}
        </div>
        <MediasBreadcrumb
            :paths="this.currentFolder.root"
            @load-folder="this.loadDataInFolder"
        >
        </MediasBreadcrumb>
      </div>
      <div v-else class="card-header text-bg-secondary">
        <i class="bi bi-trash"></i> aa Corbeille
      </div>
      <div class="card-body">
        <div v-if="render !== 'trash'">
          <div class="btn btn-secondary me-1" @click="this.openModalFolder()">
            <i class="bi bi-folder-plus"></i>
            <span class="d-none-mini">&nbsp;{{ this.translate.btn_new_folder }}</span>
          </div>
          <div class="btn btn-secondary me-1" @click="this.openModalUpload()">
            <i class="bi bi-file-plus"></i>
            <span class="d-none-mini">&nbsp;{{ this.translate.btn_new_media }}</span>
          </div>


          <div class="float-end">
            <div class="input-group float-start me-1" style="width: 250px">
              <button class="btn btn-secondary" type="button" id="button-addon1"><i class="bi bi-search"></i></button>
              <input type="text" class="form-control" :placeholder="this.translate.search_placeholder">
            </div>
            <div class="btn-group">
              <button type="button" class="btn btn-secondary dropdown-toggle me-1" data-bs-toggle="dropdown"
                  aria-expanded="false">
                <span class="d-none-mini">{{ this.translate.btn_filtre }}</span>&nbsp;<i class="bi" :class="this.filterIcon"></i>
              </button>
              <ul class="dropdown-menu">
                <li>
                  <a class="dropdown-item" href="#" @click="this.changeFilter('created_at')"><i class="bi bi-clock"></i>
                    {{ this.translate.filtre_date }}</a>
                </li>
                <li>
                  <a class="dropdown-item" href="#" @click="this.changeFilter('name')"><i class="bi bi-card-text"></i>
                    {{ this.translate.filtre_nom }}</a>
                </li>
                <li>
                  <a class="dropdown-item" href="#" @click="this.changeFilter('type')"><i class="bi bi-file"></i>
                    {{ this.translate.filtre_type }}</a>
                </li>
              </ul>
            </div>
            <div class="btn-group">
              <button type="button" class="btn btn-secondary dropdown-toggle me-1" data-bs-toggle="dropdown"
                  aria-expanded="false">
                <i class="bi" :class="this.orderIcon"></i>
              </button>
              <ul class="dropdown-menu">
                <li>
                  <a class="dropdown-item" href="#" @click="this.changeOrder('asc')"><i class="bi bi-sort-down"></i>
                    {{ this.translate.order_asc }}</a>
                </li>
                <li>
                  <a class="dropdown-item" href="#" @click="this.changeOrder('desc')"><i class="bi bi-sort-up"></i>
                    {{ this.translate.order_desc }}</a>
                </li>
              </ul>
            </div>

            <div class="btn btn-secondary position-relative me-1" @click="this.switchRender('trash');this.loadInTrash()" v-if="this.render !== 'trash'">
              <i class="bi bi-trash-fill"></i>
              <span v-if="this.nbTrash > 0" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
              {{ this.nbTrash }}
              </span>
            </div>

            <input type="radio" class="btn-check no-control" name="options-render" id="btn-grid" autocomplete="off"
                checked @change="this.switchRender('grid')">
            <label class="btn me-1 btn-secondary" for="btn-grid"><i class="bi bi-grid"></i></label>
            <input type="radio" class="btn-check no-control" name="options-render" id="btn-list" autocomplete="off"
                @change="this.switchRender('list')">
            <label class="btn btn-secondary" for="btn-list"><i class="bi bi-list"></i></label>
          </div>
        </div>
        <div v-else>
          <div class="btn btn-secondary me-1" @click="this.switchRender('grid');this.loadMedia()">
            <i class="bi bi-x-circle"></i> Fermer corbeille
          </div>
        </div>

        <div v-if="render !== 'trash'">
          <medias-grid
              :render="this.render"
              :medias="this.medias"
              :translate="this.translate.media"
              @load-data-folder="this.loadDataInFolder"
              @edit-folder="this.editFolder"
              @show-info="this.loadDataInformation"
              @edit-media="this.editMedia"
              @move="this.loadListFolderMove"
              @trash="this.confirmTrash"
          >
          </medias-grid>
        </div>

        <div v-else>
          trash
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
            <i class="bi" :class="isEmpty(this.folderEdit)?'bi-folder-plus': 'bi-pencil-fill'"></i>
            {{ this.getTitleModalFolder() }}
          </h1>
          <button type="button" class="btn-close" @click="this.closeModalFolder()"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3" :class="this.folderSuccess !== '' ? 'd-none':''">
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
          <div class="mb-3" :class="this.folderSuccess === '' ? 'd-none':''">
            <div v-if="this.folderSuccess === this.translate.folder.msg_wait_create">
              <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              {{ this.folderSuccess }}
            </div>
            <div v-else-if="this.folderSuccess === this.translate.folder.msg_wait_edit">
              <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              {{ this.folderSuccess }}
            </div>
            <span v-else class="text-success"><i class="bi bi-check"></i> <i>{{ this.folderSuccess }}</i></span>
          </div>
        </div>
        <div v-if="this.folderSuccess === ''" class="modal-footer">
          <div class="btn btn-dark" @click="this.closeModalFolder()">{{ this.translate.folder.btn_cancel }}</div>
          <div v-if="isEmpty(this.folderEdit)" @click="this.submitFolder()" class="btn btn-primary"
              :class="this.folderCanSubmit ? '':'disabled'">
            {{ this.translate.folder.btn_submit_create }}
          </div>
          <div v-else class="btn btn-primary" @click="this.submitFolder()"
              :class="this.folderCanSubmit ? '':'disabled'">
            {{ this.translate.folder.btn_submit_edit }}
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Fin Modal pour la gestion des dossier -->

  <!-- Modal pour les informations -->
  <div class="modal fade" id="modal-info" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <media-modal-info
            :data="this.infoData"
            :translate="this.translate.info"
            @close-modale="this.closeModalInfo"
        >
        </media-modal-info>
      </div>
    </div>
  </div>
  <!-- Fin Modal pour les informations -->

  <!-- Modal pour l'upload -->
  <div class="modal fade" id="modal-upload" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div v-if="this.loadingUploadMsg === ''" class="modal-content">
        <FileUpload :translate="this.translate.upload"
            :maxSize="20"
            :accept="this.extAccept"
            @file-uploaded="getUploadedData"
            @close-modale-upload="closeModalUpload"
        />
      </div>
      <div v-else class="modal-content">
        <div class="modal-header bg-secondary">
          <h1 class="modal-title fs-5 text-white">
            <i class="bi bi-upload"></i> {{ this.translate.upload.title }}
          </h1>
        </div>
        <div class="modal-body">
          {{ this.loadingUploadMsg }}
        </div>
      </div>
    </div>
  </div>
  <!-- Fin Modal pour l'upload -->

  <!-- Modal pour l'édition d'un media -->
  <div class="modal fade" id="modal-edit-media" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-secondary">
          <h1 class="modal-title fs-5 text-white">
            <i class="bi bi-pencil-square"></i>
            {{ this.translate.edit_media.title }}
          </h1>
          <button type="button" class="btn-close" @click="this.closeModalEditMedia()"></button>
        </div>
        <div class="modal-body">
          <div v-if="this.mediaEdit.status === ''" class="row">
            <div class="col-8">
              <fieldset>
                <legend> {{ this.translate.edit_media.legend }}</legend>

                <div class="mb-3">
                  <label for="edit-media-title" class="form-label"> {{ this.translate.edit_media.media_name }}</label>
                  <input type="text" class="form-control no-control" v-model="this.mediaEdit.name" id="edit-media-title"
                      :placeholder="this.translate.edit_media.media_name_placeholder">
                </div>
                <div class="mb-3">
                  <label for="edit-media-description" class="form-label"> {{ this.translate.edit_media.media_description }}</label>
                  <input type="text" class="form-control no-control" v-model="this.mediaEdit.description" id="edit-media-description"
                      :placeholder="this.translate.edit_media.media_description_placeholder">
                </div>
              </fieldset>
            </div>
            <div class="col-4 d-flex justify-content-center align-items-center text-center">
              <img :src="this.mediaEdit.thumbnail" :alt="mediaEdit.name" class="img-fluid">
            </div>
          </div>
          <div v-else-if="this.mediaEdit.status === 'loading'">
            <div class="spinner-border" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            {{ this.translate.edit_media.loading }}
          </div>
          <div v-else>
            <span class="text-success"><i class="bi bi-check"></i> {{ this.translate.edit_media.success }}</span>
          </div>

        </div>
        <div class="modal-footer">
          <div class="btn btn-secondary" @click="this.saveMedia()"> {{ this.translate.edit_media.submit }}</div>
          <div class="btn btn-dark" @click="this.closeModalEditMedia()"> {{ this.translate.edit_media.cancel }}</div>
        </div>
      </div>
    </div>
  </div>
  <!-- Fin Modal pour l'édition d'un media -->

  <!-- Modal pour le move -->
  <div class="modal fade" id="modal-move" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div v-if="this.mediaMoveStatus === ''">
          <media-move
              :translate="this.translate.move"
              :data-move="this.dataMove"
              @move="this.moveMedia"
              @close-modale="this.closeModalMove"
          />
        </div>
        <div v-else>
          <div class="modal-header bg-secondary">
            <h1 class="modal-title fs-5 text-white">
              <i class="bi bi-arrow-right-circle-fill"></i> {{ this.translate.move.title }}
            </h1>
          </div>
          <div class="modal-body">
            <div v-if="this.mediaMoveStatus === 'loading'">
              <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              {{ this.translate.move.loading }}
            </div>
            <div v-else>
              <span class="text-success"><i class="bi bi-check"></i> {{ this.translate.move.success }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Fin Modal pour le move -->

  <!-- Modal confirme corbeille -->
  <div class="modal fade" id="modal-trash" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-secondary">
          <h1 class="modal-title fs-5 text-white">
            <i class="bi bi-trash-fill"></i> {{ this.translate.trash.title }}
          </h1>
          <button v-if="!this.trashConfirm" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" v-html="this.trashMsg">
        </div>
        <div class="modal-footer" v-if="!this.trashConfirm">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ this.translate.trash.btn_cancel }}</button>
          <button type="button" class="btn btn-primary" @click="this.confirmTrash(this.trash.type, this.trash.id, '', true)">{{ this.translate.trash.btn_confirm }}</button>
        </div>
      </div>
    </div>
  </div>

</template>

<style scoped>

</style>