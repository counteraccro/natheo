<script>/**
 * Permet d'ajouter ou éditer un menu
 * @author Gourdon Aymeric
 * @version 1.O
 */
import axios from "axios";
import Toast from "../../../../Components/Global/Toast.vue";
import Modal from "../../../../Components/Global/Modal.vue";
import MenuFooter from "../../../../Components/Menu/MenuFooter.vue";
import MenuHeader from "../../../../Components/Menu/MenuHeader.vue";
import MenuLeftRight from "../../../../Components/Menu/MenuLeftRight.vue";
import MenuTree from "../../../../Components/Menu/MenuTree.vue";
import FieldEditor from "../../../../Components/Global/FieldEditor.vue";
import {emitter} from "../../../../../utils/useEvent";
import MenuForm from "../../../../Components/Menu/MenuForm.vue";
import {MenuElementTools} from "../../../../../utils/Admin/Content/Menu/MenuElementsTools";

export default {
  name: 'Menu',
  components: {
    MenuForm,
    FieldEditor,
    MenuTree,
    MenuLeftRight,
    MenuHeader,
    MenuFooter,
    Toast, Modal
  },
  props: {
    urls: Object,
    translate: Object,
    locales: Object,
    menu_datas: Object,
    id: Number
  },
  emits: [],
  data() {
    return {
      loading: false,
      menu: [],
      dataMenu: [],
      currentLocale: '',
      currentPosition: '',
      currentDeep: 0,
      listTypeByPosition: [],
      listValidParent: [],
      selectComponent: '',
      selectMenuElement: [],
      positions: [],
      showForm: false,
      labelDisabled: '',
      labelDefaultMenu: '',
      canSave: true,
      isValideName: true,
      isErrorNoElement: false,
      idToDelete: 0,
      modalTab: {
        deleteMenuElement: false,
      },
      toasts: {
        toastSuccess: {
          show: false,
          msg: '',
        },
        toastError: {
          show: false,
          msg: '',
        },
        toastAutoSave: {
          show: false,
          msg: '',
        }
      },
    }
  },
  mounted() {
    this.currentLocale = this.locales.current;
    this.loadMenu();
    emitter.on('new-menu-element', async (params) => {
      this.currentDeep = params.deep;
      this.newElement(params.id);
    });

    emitter.on('update-menu-element', async (params) => {
      this.currentDeep = params.deep;
      this.updateElement(params.id);
    });

    emitter.on('delete-menu-element', async (id) => {
      this.idToDelete = id
      this.deleteElement(true);
    });
  },
  computed: {},
  methods: {

    /**
     * event pour détecter le dropdown et le masqyer
     * @param e
     */
    handleClick(e) {
      const elt = e.target.closest(".dropdown-toggle");
      if (elt) {
        let el = elt.nextElementSibling
        el.style.display = el.style.display === 'block' ? 'none' : 'block'
      } else {
        let el = document.getElementsByClassName("dropdown-toggle");
        for (let item of el) {
          let next = item.nextElementSibling
          next.style.display = 'none';
        }
      }
    },


    /**
     * Permet de changer la locale pour la création/édition d'une page
     * @param event
     */
    switchLocale(event) {
      this.currentLocale = event.target.value;
    },


    /**
     * Permet de changer de position
     */
    switchPosition() {
      this.selectListTypeByPosition(this.menu.position);
    },

    /**
     * Permet de changer de composant
     * @param idPosition
     */
    switchComposant(idPosition) {
      switch (parseInt(idPosition)) {
        case 1 :
          this.selectComponent = 'MenuHeader';
          break;
        case 2 :
        case 4 :
          this.selectComponent = 'MenuLeftRight';
          break;
        case 3:
          this.selectComponent = 'MenuFooter';
          break;
        default:
          this.selectComponent = 'MenuHeader'
          break;
      }
    },

    /**
     * Sélectionne la liste de type en fonction de la position
     * @param position
     */
    selectListTypeByPosition(position) {

      // Premier chargement car listType est forcement vide
      let isFirstLoad = false;
      if (this.listTypeByPosition.length === 0) {
        isFirstLoad = true;
      }

      this.listTypeByPosition = [];

      for (let key in this.menu_datas.list_type) {
        if (!this.menu_datas.list_position.hasOwnProperty(key)) continue;
        if (key === position.toString()) {
          this.listTypeByPosition = this.menu_datas.list_type[key];
          break;
        }
      }

      if (!isFirstLoad && !(this.menu.type in this.listTypeByPosition)) {
        let first = Object.entries(this.listTypeByPosition)[0];
        this.menu.type = first[0];
      }
      this.dataMenu.position = position;

      this.switchComposant(position);
    },


    /**
     * Charge le menu
     * Si forceUpdate est à true, affiche le formulaire du menuElemet défini par id
     */
    loadMenu(idToOpen) {
      let url = this.urls.load_menu + '/' + this.id;
      if (this.id === null) {
        url = this.urls.load_menu;
      }
      this.loading = true;
      axios.get(url, {}
      ).then((response) => {

        this.menu = response.data.menu;
        this.dataMenu = response.data.data;
        this.selectListTypeByPosition(this.menu.position);
        this.renderLabelDisabled();
        this.renderLabelDefaultMenu();

        if (this.menu.id === "") {
          this.canSave = false;
        }

        if (Number.isInteger(idToOpen) && idToOpen > 0) {
          this.updateElement(idToOpen)
        }

      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.loading = false
      });
    },

    /**
     * Permet de sauvegarder un menu
     */
    saveMenu() {

      if (!this.verifCanSave()) {
        return false;
      }

      this.loading = true;
      axios.post(this.urls.save_menu, {
            'menu': this.menu
          }
      ).then((response) => {

        if (response.data.success === true) {
          this.toasts.toastSuccess.msg = response.data.msg;
          this.toasts.toastSuccess.show = true;
          // Cas première page, on force la redirection pour passer en mode édition
          if (response.data.redirect === true) {
            window.location.replace(response.data.url);
          }
        } else {
          this.toasts.toastError.msg = response.data.msg;
          this.toasts.toastError.show = true;
        }

      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.loading = false
        emitter.emit('reset-check-confirm');
      });
    },

    /**
     * Vérification si on peut lancer la sauvegarde ou non
     * @returns {boolean}
     */
    verifCanSave() {
      if (Object.entries(this.menu.menuElements).length === 0 && this.menu.id !== "") {
        this.isErrorNoElement = true;
        return false;
      }
      return this.canSave;
    },

    /**
     * Mise à jour d'un élément
     * @param id
     */
    updateElement(id) {

      this.loading = true;
      axios.get(this.urls.list_parent_menu_element + '/' + this.menu.id + '/' + id).then((response) => {
        this.listValidParent = response.data.listParent;

        let element = MenuElementTools.getElementMenuById(this.menu.menuElements, id);
        if (element === null) {
          console.warn(`id ${id} not found in menuElement`);
          this.showForm = false;
        } else {
          if (element.hasOwnProperty('parent')) {
            this.positions = MenuElementTools.calculMaxColAndRowMaxByIdParent(this.menu.menuElements, element.parent);
          } else {
            this.positions = MenuElementTools.calculMaxColAndRowMaxByIdParent(this.menu.menuElements, null);
          }
          this.selectMenuElement = element;
          this.showForm = true;
        }

      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.loading = false;
      });

    },

    /**
     * Permet de fermer le formulaire d'édition
     */
    closeForm() {
      this.showForm = false;
      this.selectMenuElement = [];
    },

    /**
     * Met à jour le parent d'un élément
     * @param id
     * @param idParent
     * @param deep
     */
    updateParent(id, idParent, deep) {

      idParent = parseInt(idParent);
      let positions = MenuElementTools.calculMaxColAndRowMaxByIdParent(this.menu.menuElements, idParent);
      console.log(positions);
      if (positions.columnMax === 0) {
        positions.columnMax = 1;
        positions[positions.columnMax] = {'colum': 1, 'rowMax': 0};
      }

      // Si la profondeur n'est pas 1 alors on force la valeur de column max
      if (deep !== 1) {
        positions.columnMax = 1;
      }

      this.loading = true;
      axios.patch(this.urls.update_parent_menu_element, {
        'id': id,
        'idParent': idParent,
        'columP': positions.columnMax,
        'rowP': (positions[positions.columnMax].rowMax) + 1,
      }).then((response) => {
        if (response.data.success === true) {
          this.toasts.toastSuccess.msg = response.data.msg;
          this.toasts.toastSuccess.show = true;
          this.currentDeep = deep;
          this.loadMenu(response.data.id);

        } else {
          this.toasts.toastError.msg = response.data.msg;
          this.toasts.toastError.show = true;
          this.loading = false
        }
      }).catch((error) => {
        console.error(error);
      }).finally(() => {
      });
    },

    /**
     * Nouvel menuElement
     * @param parent
     */
    newElement(parent) {

      if (parent === 0) {
        parent = null;
      }
      let positions = MenuElementTools.calculMaxColAndRowMaxByIdParent(this.menu.menuElements, parent);
      if (positions.columnMax === 0) {
        positions.columnMax = 1;
        positions[positions.columnMax] = {'colum': 1, 'rowMax': 0};
      }

      if (this.currentDeep !== 1) {
        positions.columnMax = 1;
      }

      this.loading = true;
      axios.post(this.urls.new_menu_element, {
        'idParent': parent,
        'idMenu': this.menu.id,
        'columP': positions.columnMax,
        'rowP': (positions[positions.columnMax].rowMax) + 1,
      }).then((response) => {
        if (response.data.success === true) {
          this.toasts.toastSuccess.msg = response.data.msg;
          this.toasts.toastSuccess.show = true;
          this.loadMenu(response.data.id);

        } else {
          this.toasts.toastError.msg = response.data.msg;
          this.toasts.toastError.show = true;
          this.loading = false
        }
      }).catch((error) => {
        console.error(error);
      }).finally(() => {
      });
    },


    /**
     * Supprime un élement
     * @param id
     * @param confirm
     */
    deleteElement(confirm) {

      if (confirm) {
        this.updateModale('deleteMenuElement', true);
        return true;
      }
      this.updateModale('deleteMenuElement', false);

      this.loading = true;
      axios.delete(this.urls.delete_menu_element + '/' + this.idToDelete).then((response) => {
        if (response.data.success === true) {
          this.toasts.toastSuccess.msg = response.data.msg;
          this.toasts.toastSuccess.show = true;
          this.selectMenuElement = [];
          this.showForm = false;
          this.loadMenu();
        } else {
          this.toasts.toastError.msg = response.data.msg;
          this.toasts.toastError.show = true;
          this.loading = false
        }

      }).catch((error) => {
        console.error(error);
      }).finally(() => {
      });


    },

    /**
     * Réordonne-les élements d'un menu
     * @param data
     */
    reorderElement(data) {

      data['menu'] = this.menu.id;

      this.loading = true;
      axios.patch(this.urls.reorder_menu_element, {
        data
      }).then((response) => {
        if (response.data.success === true) {
          this.toasts.toastSuccess.msg = response.data.msg;
          this.toasts.toastSuccess.show = true;
          this.loadMenu(response.data.id);
          //this.loading = true;

        } else {
          this.toasts.toastError.msg = response.data.msg;
          this.toasts.toastError.show = true;
          this.loading = false
        }
      }).catch((error) => {
        console.error(error);
      }).finally(() => {
      });

    },

    /**
     * Rendu du label pour la checkbox disabled
     * @returns {*}
     */
    renderLabelDisabled() {
      if (this.menu.disabled) {
        this.labelDisabled = this.translate.checkbox_disabled_label_msg;
      } else {
        this.labelDisabled = this.translate.checkbox_enabled_label_msg;
      }
    },

    /**
     * Rendu du label pour la checkbox disabled
     * @returns {*}
     */
    renderLabelDefaultMenu() {
      if (!this.menu.defaultMenu) {
        this.labelDefaultMenu = this.translate.checkbox_default_menu_true_label_msg;
      } else {
        this.labelDefaultMenu = this.translate.checkbox_default_menu_false_label_msg;
      }
    },

    /**
     * Met à jour le status d'une modale défini par son id et son état
     * @param nameModale
     * @param state true|false
     */
    updateModale(nameModale, state) {
      this.modalTab[nameModale] = state;
    },

    /**
     * Ferme une modale
     * @param nameModale
     */
    closeModal(nameModale) {
      this.updateModale(nameModale, false);
    },


    /**
     * Ferme le toast défini par nameToast
     * @param nameToast
     */
    closeToast(nameToast) {
      this.toasts[nameToast].show = false
    },

    /**
     * Test si le nom du menu est vide ou non
     */
    isEmptyName() {
      if (this.menu.name === '') {
        this.isValideName = false;
        this.canSave = false;
      } else {
        this.isValideName = true;
        this.canSave = true;
      }
    }
  }
}


</script>

<template>

  <div id="global-menu" :class="this.loading === true ? 'block-grid' : ''" @click="handleClick">

    <div v-if="this.loading" class="overlay">
      <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1000;">
        <div class="spinner-border text-primary" role="status"></div>
        <span class="txt-overlay">{{ this.translate.loading }}</span>
      </div>
    </div>

    <select id="select-language" class="form-select w-auto float-end" @change="this.switchLocale($event)">
      <option value="" selected>{{ this.translate.select_locale }}</option>
      <option v-for="(language, key) in this.locales.localesTranslate" :value="key"
          :selected="key===this.currentLocale">{{ language }}
      </option>
    </select>
    <div class="clearfix"></div>

    <div class="mt-5" v-if="this.menu.length === 0">
      <div class="text-center">
        <i>{{ this.translate.msg_wait_loading }}</i>
      </div>
    </div>
    <div v-else class="block-create-menu mt-2">

      <fieldset>
        <legend>{{ this.translate.title_demo }}</legend>
        <Component :is="this.selectComponent" class="mb-5 mt-2"
            :menu="this.menu"
            :type="parseInt(this.menu.type)"
            :locale="this.currentLocale"
            :data="this.dataMenu"
        />

        <div class="clearfix"></div>
        <i>{{ this.translate.title_demo_warning }}</i>
      </fieldset>

      <fieldset class="mt-2">
        <legend>{{ this.translate.title_global_form }}</legend>


        <div class="w-100">
          <div v-if="this.id !== null" class="btn btn-secondary float-end" :class="!this.canSave ? 'disabled' : ''" @click="this.saveMenu">
            <i class="bi bi-floppy-fill"></i>
            {{ this.translate.btn_save }}
          </div>
          <div v-else class="btn btn-secondary float-end" :class="!this.canSave ? 'disabled' : ''" @click="this.saveMenu">
            <i class="bi bi-menu-button-wide-fill"></i>
            {{ this.translate.btn_new }}
          </div>
        </div>
        <div class="clearfix"></div>

        <div class="card border border-secondary mt-3 mb-3">
          <div class="card-header text-bg-secondary">
            {{ this.translate.title_generic_data }}
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col">
                <div class="mb-3">
                  <label for="menu-title" class="form-label">{{ this.translate.input_name_label }}</label>
                  <input type="text" class="form-control" :class="!this.isValideName ? 'is-invalid' : ''" id="menu-title"
                      v-model="this.menu.name" :placeholder="this.translate.input_name_placeholder" @change="this.isEmptyName()">
                  <div class="invalid-feedback">
                    {{ this.translate.input_name_error }}
                  </div>
                </div>

                <div class="mb-3">

                  <div class="form-check form-check-inline">
                    <input class="form-check-input" v-model="this.menu.defaultMenu" type="radio" name="defaultMenu" id="default-menu-false" :value="false" @change="this.renderLabelDefaultMenu">
                    <label class="form-check-label" for="default-menu-false"> {{ this.translate.checkbox_default_menu_false_label }}</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" v-model="this.menu.defaultMenu" type="radio" name="defaultMenu" id="default-menu-true" :value="true" @change="this.renderLabelDefaultMenu">
                    <label class="form-check-label" for="default-menu-true">{{ this.translate.checkbox_default_menu_true_label }}</label>
                  </div>

                  <div class="clearfix"></div>
                  <div class="mt-1">
                    <i> {{ this.labelDefaultMenu }} </i>
                  </div>
                </div>

                <div class="mb-3">

                  <div class="form-check form-check-inline">
                    <input class="form-check-input" v-model="this.menu.disabled" type="radio" name="menuDisabled" id="menu-enabled" :value="false" @change="this.renderLabelDisabled">
                    <label class="form-check-label" for="menu-enabled"> {{ this.translate.checkbox_enabled_label }}</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" v-model="this.menu.disabled" type="radio" name="menuDisabled" id="menu-disabled" :value="true" @change="this.renderLabelDisabled">
                    <label class="form-check-label" for="menu-disabled">{{ this.translate.checkbox_disabled_label }}</label>
                  </div>

                  <div class="clearfix"></div>
                  <div class="mt-1">
                    <i> {{ this.labelDisabled }} </i>
                  </div>
                </div>

              </div>
              <div class="col">
                <div class="mb-3">
                  <label for="menu-position" class="form-label">{{ this.translate.select_position_label }}</label>
                  <select id="menu-position" class="form-select" v-model="this.menu.position" @change="this.switchPosition($event)">
                    <option v-for="(position, key) in this.menu_datas.list_position" :value="key">{{ position }}
                    </option>
                  </select>
                </div>

                <div class="mb-3">
                  <label for="menu-type" class="form-label">{{ this.translate.select_type_label }}</label>
                  <select id="menu-type" class="form-select" v-model="this.menu.type" :disabled="this.listTypeByPosition.length === 0">
                    <option value="" selected v-if="this.listTypeByPosition.length === 0">
                      {{ this.translate.select_type }}
                    </option>
                    <option v-for="(position, key) in this.listTypeByPosition" :value="key">{{ position }}
                    </option>
                  </select>
                </div>

                <div class="mb-3">
                  <label for="menu-page" class="form-label">{{ this.translate.select_page_label }}</label>
                  <select id="menu-page" class="form-select" size="10" v-model="this.menu.pageMenu" multiple>
                    <option value="-1">{{ this.translate.select_page_no_page }}</option>
                    <option v-for="(page, id) in this.dataMenu.pages" :value=id>{{ page[this.currentLocale]['title'] }}
                    </option>
                  </select>
                  <div class="form-text">
                    {{ this.translate.select_page_info }}
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>

        <div class="row">
          <div class="col-4">

            <div class="card border" :class="this.isErrorNoElement ? 'border-danger' : 'border-secondary'">
              <div class="card-header" :class="this.isErrorNoElement ? 'text-bg-danger' : 'text-bg-secondary'">
                {{ this.translate.title_architecture }}
              </div>
              <div class="card-body">

                <div v-if="this.menu.id !== ''">
                  <ul class=" tree-menu">
                    <menu-tree
                        v-for="menuElement in this.menu.menuElements"
                        :menu-element="menuElement"
                        :locale="this.currentLocale"
                        :id-select="this.selectMenuElement.id"
                        :translate="this.translate.menu_tree"
                        :deep="0"
                    />
                    <li>
                      <div>
                          <span class="btn btn-outline-secondary btn-sm" @click="this.newElement(0)"><i class="bi bi-plus-square"></i>
                            {{ this.translate.btn_new_menu_element }}
                          </span>
                      </div>
                    </li>
                  </ul>

                  <div class="text-danger" v-if="this.isErrorNoElement">
                    <i class="bi bi-exclamation-triangle-fill"></i> <i>{{ this.translate.error_no_element }}</i>
                  </div>
                </div>
                <div v-else>
                  <i class="bi bi-info-circle"></i> {{ this.translate.msg_no_element_new_menu }}
                </div>
              </div>
            </div>
          </div>
          <div class="col-8">

            <menu-form
                v-if="this.showForm"
                :menu-element="this.selectMenuElement"
                :translate="this.translate.menu_form"
                :locale="this.currentLocale"
                :pages="this.dataMenu.pages"
                :positions="this.positions"
                :all-elements="this.listValidParent"
                :deep="this.currentDeep"
                @reorder-element="this.reorderElement"
                @change-parent="this.updateParent"
                @close-form="this.closeForm"
            >
            </menu-form>

            <div v-else class="card border border-secondary h-100">
              <div class="card-header text-bg-secondary">
                {{ this.translate.no_select_menu_form }}
              </div>
              <div class="card-body">
                <p class="text-black"><i>{{ this.translate.no_select_menu_form_msg }}</i></p>

                {{ this.translate.help_title }} <br/>

                <i class="bi bi-arrow-right"></i> <i class="bi bi-pencil-fill"></i> {{ this.translate.help_edition }}
                <br/>
                <i class="bi bi-arrow-right"></i> <i class="bi bi-x-lg"></i> {{ this.translate.help_delete }} <br/>
                <i class="bi bi-arrow-right"></i> <i class="bi bi-plus-square"></i> {{ this.translate.help_new }}
                <br/>
                <i class="bi bi-arrow-right"></i>
                <i class="bi bi-eye-slash-fill"></i> {{ this.translate.help_disabled }}

              </div>
            </div>
          </div>
        </div>
      </fieldset>
    </div>
  </div>

  <!-- modale confirmation suppression -->
  <modal
      :id="'deleteMenuElement'"
      :show="this.modalTab.deleteMenuElement"
      @close-modal="this.closeModal"
      :option-show-close-btn="false">
    <template #title>
      <i class="bi bi-sign-stop-fill"></i>&nbsp;
                                          {{ this.translate.menu_element_confirm_delete_title }}
    </template>
    <template #body>
      {{ this.translate.menu_element_confirm_delete_body }}
    </template>
    <template #footer>
      <button type="button" class="btn btn-primary" @click="this.deleteElement(false)">
        <i class="bi bi-check2-circle"></i> {{ translate.menu_element_confirm_delete_btn_ok }}
      </button>
      <button type="button" class="btn btn-secondary" @click="this.closeModal('deleteMenuElement')">
        <i class="bi bi-x-circle"></i> {{ translate.menu_element_confirm_delete_btn_ko }}
      </button>
    </template>
  </modal>
  <!-- fin modale confirmation suppression -->


  <!-- toast -->
  <div class="toast-container position-fixed top-0 end-0 p-2">

    <toast
        :id="'toastSuccess'"
        :option-class-header="'text-success'"
        :show="this.toasts.toastSuccess.show"
        @close-toast="this.closeToast"
    >
      <template #header>
        <i class="bi bi-check-circle-fill"></i> &nbsp;
        <strong class="me-auto"> {{ this.translate.toast_title_success }}</strong>
        <small class="text-black-50">{{ this.translate.toast_time }}</small>
      </template>
      <template #body>
        <div v-html="this.toasts.toastSuccess.msg"></div>
      </template>
    </toast>

    <toast
        :id="'toastError'"
        :option-class-header="'text-danger'"
        :show="this.toasts.toastError.show"
        @close-toast="this.closeToast"
    >
      <template #header>
        <i class="bi bi-exclamation-triangle-fill"></i> &nbsp;
        <strong class="me-auto"> {{ this.translate.toast_title_error }}</strong>
        <small class="text-black-50">{{ this.translate.toast_time }}</small>
      </template>
      <template #body>
        <div v-html="this.toasts.toastError.msg"></div>
      </template>
    </toast>
  </div>

</template>