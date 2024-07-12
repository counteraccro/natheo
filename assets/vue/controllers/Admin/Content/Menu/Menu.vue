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
      listTypeByPosition: [],
      selectComponent: 'MenuHeader',
      selectMenuElement: [],
      positions: [],
      showForm: false,
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
    emitter.on('new-menu-element', async (id) => {
      this.newElement(id);
    });

    emitter.on('update-menu-element', async (id) => {
      this.updateElement(id);

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

      switch (idPosition) {
        case "1" :
          this.selectComponent = 'MenuHeader';
          break;
        case "2" :
        case "4" :
          this.selectComponent = 'MenuLeftRight';
          break;
        case "3":
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

      this.listTypeByPosition = [];

      for (let key in this.menu_datas.list_type) {
        if (!this.menu_datas.list_position.hasOwnProperty(key)) continue;
        if (key === position.toString()) {
          this.listTypeByPosition = this.menu_datas.list_type[key];
          break;
        }
      }
      this.switchComposant(position);
    },


    /**
     * Charge le menu
     */
    loadMenu() {
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
      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.loading = false
      });
    },

    /**
     * Retourne un menuElement en fonction de son id
     * @param elements
     * @param id
     * @returns {null}
     */
    getElementMenuById(elements, id) {

      let element = null;

      Object.entries(elements).forEach((value) => {
        let obj = value[1];
        if (obj.id === id) {
          element = obj;
        } else if (obj.hasOwnProperty('children') && obj.children.menuElements.length && element === null) {
          element = this.getElementMenuById(obj.children.menuElements, id)
        }
      });
      return element;
    },

    /**
     * Calcul la valeur de columnMax et rowMax en fonction du parent
     * @param elements
     * @param idParent
     */
    calculMaxColAndRowMaxByIdParent(elements, idParent) {

      /**
       * Fonction temporaire pour calculer columnMax et rowMax en fonction
       * de la column
       * @param data
       * @param obj
       * @returns {*}
       */
      function tmp(data, obj) {
        if (obj.columnPosition > data.columnMax) {
          data.columnMax = obj.columnPosition;
          data[obj.columnPosition] = {'colum': obj.columnPosition, 'rowMax': 0};
        }

        if (obj.rowPosition > data[obj.columnPosition].rowMax) {
          data[obj.columnPosition].rowMax = obj.rowPosition;
        }
        return data;
      }

      let data = {'columnMax': 0}
      Object.entries(elements).forEach((value) => {
        let obj = value[1];
        if (obj.hasOwnProperty('parent') && idParent === obj.parent) {
          data = tmp(data, obj);
        } else if (obj.hasOwnProperty('children') && obj.children.menuElements.length && idParent !== null && data.columnMax === 0) {
          data = this.calculMaxColAndRowMaxByIdParent(obj.children.menuElements, idParent);
        } else if (idParent === null) {
          data = tmp(data, obj);
        }
      });

      return data;
    },

    /**
     * Mise à jour des données du menu
     * @param value
     * @param id
     */
    updateValueMenu(value, id) {
      this.showForm = true;
      console.log('id' + id + ' value : ' + value);
    },

    /**
     * Mise à jour d'un élément
     * @param id
     */
    updateElement(id) {
      let element = this.getElementMenuById(this.menu.menuElements, id);
      if (element === null) {
        console.warn(`id ${id} not found in menuElement`);
        this.showForm = false;
      } else {
        if (element.hasOwnProperty('parent')) {
          this.positions = this.calculMaxColAndRowMaxByIdParent(this.menu.menuElements, element.parent);
        } else {
          this.positions = this.calculMaxColAndRowMaxByIdParent(this.menu.menuElements, null);
        }
        console.log(this.positions);

        this.selectMenuElement = element;
        this.showForm = true;
      }

    },

    /**
     * Nouvel élément
     * @param parent
     */
    newElement(parent) {
      console.log('new element menu.vue parent : ' + parent);
    },

    /**
     * Réordonne-les élements d'un menu
     * @param data
     */
    reorderElement(data) {
      function reorderByRowPosition(a, b) {
        return (a[1]['columnPosition'] - b[1]['columnPosition']) || (a[1]['rowPosition'] - b[1]['rowPosition']);
      }

      console.log(data);

      let elements = this.menu.menuElements;
      if (data.parent !== 0) {
        let tmp = this.getElementMenuById(this.menu.menuElements, data.parent);
        elements = tmp.children.menuElements;
      } else {
        //console.log(this.menu.menuElements);
        //console.log(elements);
      }

      console.log(elements);

      // Traitement du champ column
      /*Object.entries(elements).forEach((value, index) => {
        let obj = value[1];
        if (obj.id !== data.id && obj.columnPosition === data.newColumn) {
          obj.rowPosition = obj.rowPosition + 1;
        } else if (obj.columnPosition !== data.newColumn) {
          obj.rowPosition = obj.rowPosition + 1
        }
      });

      // Traitement du champ row
      Object.entries(elements).forEach((value, index) => {
        let obj = value[1];
        if (obj.columnPosition === data.newColumn) {
          if (obj.id !== data.id && obj.rowPosition === data.newRow) {
            obj.rowPosition = obj.rowPosition + 1;
          } else if (obj.rowPosition !== data.newRow) {
            obj.rowPosition = obj.rowPosition + 1
          }
        }
      });*/

      console.log(elements);

      elements = Object.entries(elements).sort(reorderByRowPosition)
      console.log(elements);
    },


    /**
     * Ferme le toast défini par nameToast
     * @param nameToast
     */
    closeToast(nameToast) {
      this.toasts[nameToast].show = false
    },
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

        <i>{{ this.translate.title_demo_warning }}</i>
      </fieldset>

      <fieldset class="mt-2">
        <legend>{{ this.translate.title_architecture }}</legend>

        <div class="row">
          <div class="col-5">
            <select id="select-position" class="form-select w-auto" v-model="this.menu.position" @change="this.switchPosition($event)">
              <option value="" selected>{{ this.translate.select_position }}</option>
              <option v-for="(position, key) in this.menu_datas.list_position" :value="key">{{ position }}
              </option>
            </select>
          </div>
          <div class="col-5">
            <select id="select-type" class="form-select w-auto" v-model="this.menu.type" :disabled="this.listTypeByPosition.length === 0">
              <option value="" selected v-if="this.listTypeByPosition.length === 0">
                {{ this.translate.select_type }}
              </option>
              <option v-for="(position, key) in this.listTypeByPosition" :value="key">{{ position }}
              </option>
            </select>
          </div>
          <div class="col-2">
            <div v-if="this.id !== null" class="btn btn-secondary float-end">
              <i class="bi bi-floppy-fill"></i>
              {{ this.translate.btn_save }}
            </div>
            <div v-else class="btn btn-secondary float-end">
              <i class="bi bi-menu-button-wide-fill"></i>
              {{ this.translate.btn_new }}
            </div>
          </div>
        </div>

        <div>{{ this.menu.name }}</div>

        <field-editor :key="1"
            class="mb-3"
            :id="'' + this.menu.id + ''"
            :p-value="this.menu.name"
            balise="h5"
            rule="isEmpty"
            :rule-msg="this.translate.error_empty_value"
            @get-value="this.updateValueMenu"
        >

        </field-editor>

        <div class="row">
          <div class="col-4">

            <div class="card border border-secondary">
              <div class="card-header text-bg-secondary">
                {{ this.translate.title_architecture }}
              </div>
              <div class="card-body">

                <ul class="tree-menu">
                  <menu-tree
                      v-for="menuElement in this.menu.menuElements"
                      :menu-element="menuElement"
                      :locale="this.currentLocale"
                      :update-element="this.updateElement"
                      :new-element="this.newElement"
                  >

                  </menu-tree>
                  <li @click="this.newElement(0)">Nouveau</li>

                </ul>
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
                @reorder-element="this.reorderElement"
            >
            </menu-form>


          </div>
        </div>

      </fieldset>


    </div>


  </div>


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