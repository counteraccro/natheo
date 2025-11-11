<script>
/**
 * Permet d'ajouter ou éditer un tag
 * @author Gourdon Aymeric
 * @version 1.1
 */

import axios from 'axios';
import { emitter } from '../../../../utils/useEvent';
import SkeletonForm from '@/vue/Components/Skeleton/Form.vue';
import Toast from '@/vue/Components/Global/Toast.vue';
import Modal from '@/vue/Components/Global/Modal.vue';

export default {
  name: 'TagForm',
  components: { Modal, Toast, SkeletonForm },
  props: {
    url: String,
    url_stats: String,
    url_index: String,
    url_update: String,
    url_delete: String,
    translate: Object,
    locales: Object,
    pTag: Object,
  },
  data() {
    return {
      loading: false,
      tag: this.pTag,
      tabColor: [],
      msgErrorExa: '',
      autoCopy: true,
      isErrorHexa: true,
      showErrors: false,
      templateStat: '',
      classNoControl: '',
      showModalConfirmDelete: false,
      toasts: {
        toastSuccess: {
          show: false,
          msg: '',
        },
        toastError: {
          show: false,
          msg: '',
        },
      },
    };
  },
  mounted() {
    this.loadColorExemple();
    if (this.tag.id !== null) {
      this.isErrorHexa = this.isErrorLabel = false;
      this.autoCopy = false;
      this.loadStat();
    }
  },
  computed: {
    isErrorLabel() {
      if (!this.showErrors) {
        return false;
      }

      return this.tag.tagTranslations.some((translation) => translation.label === '' || translation.label === null);
    },
  },

  methods: {
    /**
     * sauvegarde des données
     */
    save() {
      this.loading = true;
      this.classNoControl = 'no-control';

      axios
        .post(this.url, {
          tag: this.tag,
        })
        .then((response) => {
          emitter.emit('reset-check-confirm');
          this.toasts.toastSuccess.msg = this.translate.successSave;
          this.toasts.toastSuccess.show = true;

          if (response.data.etat === 'new') {
            window.location = this.url_index;
          }
        })
        .catch((error) => {
          console.error(error);
          this.toasts.toastError.msg = this.translate.errorSave;
          this.toasts.toastError.show = true;
        })
        .finally(() => {
          this.loadStat();
          this.loading = false;
        });
    },

    loadStat() {
      axios
        .get(this.url_stats)
        .then((response) => {
          this.templateStat = response.data;
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {});
    },

    /**
     * Supprime un tag
     */
    ajaxDelete() {
      this.loading = true;
      this.showModalConfirmDelete = false;

      axios
        .delete(this.url_delete)
        .then((response) => {
          if (response.data.success === true) {
            this.toasts.toastSuccess.msg = response.data.msg;
            this.toasts.toastSuccess.show = true;
          } else {
            this.toasts.toastError.msg = response.data.msg;
            this.toasts.toastError.show = true;
          }
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          setTimeout(() => {
            window.location = this.url_index;
          }, 1500);
        });
    },

    /**
     * Charge une liste de 5 couleurs aléatoires
     */
    loadColorExemple() {
      let i;
      this.tabColor = [];
      for (i = 0; i < 5; i++) {
        this.tabColor.push(this.generateRandomHexColor());
      }
    },

    /**
     * Vérifie si la valeur hexadecimal est correcte
     */
    checkValideHex() {
      this.msgErrorExa = '';
      this.isErrorHexa = false;

      let reg = /^#([0-9a-f]{3}){1,2}$/i;
      if (!reg.test(this.tag.color)) {
        this.msgErrorExa = this.translate.formInputColorError;
        this.isErrorHexa = true;
      }
    },

    /**
     * Affichage du label du bouton
     * @returns {*}
     */
    getLabelSubmit() {
      if (this.tag.id === null) {
        let icon =
          '<svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> ';

        return icon + this.translate.btnSubmitCreate;
      }

      let icon =
        '<svg class="icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="2" d="M10.779 17.779 4.36 19.918 6.5 13.5m4.279 4.279 8.364-8.643a3.027 3.027 0 0 0-2.14-5.165 3.03 3.03 0 0 0-2.14.886L6.5 13.5m4.279 4.279L6.499 13.5m2.14 2.14 6.213-6.504M12.75 7.04 17 11.28"></path></svg>';
      return icon + this.translate.btnSubmitUpdate;
    },

    /**
     * Permet de changer la couler choisie
     * @param color
     */
    switchColor(color) {
      this.isErrorHexa = false;
      this.msgErrorExa = '';
      this.tag.color = color;
    },

    /**
     * Duplique les labels en fonction de la valeur du label du current locale
     * @param label
     */
    copyLabel(label) {
      this.locales.locales.forEach((locale) => {
        this.tag.tagTranslations.forEach((translation) => {
          if (locale === translation.locale && translation.locale !== this.locales.current) {
            translation.label = label;
          }
        });
      });
    },

    /**
     * Active ou désactive un champ
     * @param locale
     * @returns {boolean}
     */
    isDisabled(locale) {
      return this.autoCopy && locale !== this.locales.current;
    },

    /**
     * Vérifie si le label n'est pas vide
     * @param translation_id
     * @returns {string}
     */
    isNoEmptyInput(translation_id) {
      let css = '';
      this.tag.tagTranslations.forEach((translation) => {
        if (translation.id === translation_id && translation.label === '') {
          css = 'is-invalid';
        }
      });
      return css;
    },

    /**
     * Génère une valeur hexadécimale random
     * @returns {*|string}
     */
    generateRandomHexColor() {
      const randomColor = '#' + Math.floor(Math.random() * 16777215).toString(16);
      if (randomColor.length !== 7) {
        return this.generateRandomHexColor();
      } else {
        return randomColor;
      }
    },

    toggleCopy() {
      this.autoCopy = !this.autoCopy;
    },

    /**
     * Active ou désactive le bouton submit
     * @returns {boolean}
     */
    canSubmit() {
      return this.isErrorHexa || this.isErrorLabel;
    },

    /**
     * Ferme le toast défini par nameToast
     * @param nameToast
     */
    closeToast(nameToast) {
      this.toasts[nameToast].show = false;
    },

    /**
     * Ferme la modale
     */
    hideModal() {
      this.showModalConfirmDelete = false;
    },
  },
};
</script>

<template>
  <div v-if="this.loading">
    <SkeletonForm />
  </div>
  <div v-else>
    <div class="flex justify-between gap-10">
      <div class="form-group w-7/12">
        <label for="tagColor" class="form-label">{{ this.translate.colorTitle }} </label>
        <div class="flex items-center gap-3">
          <input
            type="color"
            @change="
              this.isErrorHexa = false;
              this.msgErrorExa = '';
            "
            class="form-color"
            id="tagColor"
            v-model="this.tag.color"
          />

          <input
            type="text"
            class="form-input flex-1"
            :class="this.msgErrorExa !== '' ? 'is-invalid' : ''"
            id="tagColorinput"
            v-model="this.tag.color"
            size="7"
            style="width: auto"
            @change="this.checkValideHex()"
            maxlength="7"
          />
        </div>
        <span v-if="this.msgErrorExa" class="form-text text-error">✗ {{ this.msgErrorExa }}</span>
        <span v-else class="form-text">Choisissez une couleur pour identifier visuellement ce tag</span>
      </div>

      <div class="w-3/12">
        <label for="tagColor" class="form-label">{{ this.translate.renduTitle }} </label>
        <div v-for="key in this.locales.locales">
          <div v-for="translation in tag.tagTranslations">
            <div v-if="translation.locale === key" class="text-[var(--text-primary)] text-sm mb-1">
              {{ this.locales.localesTranslate[key] }} :
              <span class="badge rounded-pill badge-nat" :style="'background-color: ' + tag.color">{{
                translation.label
              }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-for="key in this.locales.locales">
      <div v-for="translation in tag.tagTranslations">
        <div v-if="translation.locale === key">
          <div class="form-group mt-4">
            <label :for="'label-' + translation.locale" class="form-label"
              >{{ this.translate.formInputLabelLabel }} {{ this.locales.localesTranslate[key] }}
            </label>
            <input
              type="text"
              :class="this.isNoEmptyInput(translation.id)"
              class="form-input"
              :id="'label-' + translation.locale"
              :placeholder="this.translate.formInputLabelPlaceholder"
              @blur="if (!translation.label) showErrors = true;"
              :disabled="this.isDisabled(translation.locale)"
              v-model="translation.label"
              v-on="
                this.autoCopy && translation.locale === this.locales.current
                  ? { keyup: () => this.copyLabel(translation.label) }
                  : {}
              "
            />

            <div class="form-switch float-end mt-2" v-if="translation.locale === this.locales.current">
              <div class="switch-input" :class="this.autoCopy ? 'active' : ''" @click="this.toggleCopy()"></div>
              <span class="switch-label" @click="this.toggleCopy()">{{ this.translate.autoCopy }}</span>
            </div>
            <span
              v-if="this.isErrorLabel && (translation.locale === this.locales.current || !this.autoCopy)"
              class="form-text text-error"
              >✗
              {{ this.translate.formInputLabelError }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="this.tag.id !== null && this.templateStat !== ''"
      class="card p-4 mb-6 mt-4"
      style="background-color: var(--bg-hover)"
    >
      <h3>
        <svg
          class="w-4 h-4 inline"
          aria-hidden="true"
          xmlns="http://www.w3.org/2000/svg"
          width="24"
          height="24"
          fill="none"
          viewBox="0 0 24 24"
        >
          <path
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M4 4v15a1 1 0 0 0 1 1h15M8 16l2.5-5.5 3 3L17.273 7 20 9.667"
          />
        </svg>

        {{ this.translate.statTitle }}
      </h3>
      <div class="form-text" v-html="this.templateStat"></div>
    </div>

    <div class="flex flex-wrap gap-3 pt-4 border-t border-[var(--border-color)] mt-5">
      <button
        class="btn btn-sm btn-primary"
        @click="this.save()"
        :disabled="this.canSubmit()"
        v-html="this.getLabelSubmit()"
      ></button>
      <button type="button" class="btn btn-outline-dark btn-md" onclick="window.history.back()">
        {{ this.translate.btnCancel }}
      </button>
      <button
        v-if="this.tag.id !== null"
        type="button"
        class="btn btn-danger btn-md ml-auto"
        @click="this.showModalConfirmDelete = true"
      >
        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
          ></path>
        </svg>
        {{ this.translate.btnDelete }}
      </button>
    </div>
  </div>

  <modal
    :id="'modal-confirm-delete'"
    :show="this.showModalConfirmDelete"
    @close-modal="this.hideModal"
    :option-show-close-btn="false"
  >
    <template #title> <i class="bi bi-sign-stop"></i> {{ translate.modaleConfirmDeleteTitle }} </template>
    <template #body>
      <div v-html="this.translate.modaleConfirmDeleteMessage"></div>
    </template>
    <template #footer>
      <button type="button" class="btn btn-primary btn-sm me-2" @click="ajaxDelete">
        <svg
          class="icon"
          aria-hidden="true"
          xmlns="http://www.w3.org/2000/svg"
          width="24"
          height="24"
          fill="none"
          viewBox="0 0 24 24"
        >
          <path
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M8.5 11.5 11 14l4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
          />
        </svg>
        {{ translate.modaleConfirmDeleteBtnOK }}
      </button>
      <button type="button" class="btn btn-outline-dark btn-sm" @click="this.hideModal()">
        <svg
          class="icon"
          aria-hidden="true"
          xmlns="http://www.w3.org/2000/svg"
          width="24"
          height="24"
          fill="none"
          viewBox="0 0 24 24"
        >
          <path
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="m15 9-6 6m0-6 6 6m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
          />
        </svg>

        {{ translate.modaleConfirmDeleteBtnKo }}
      </button>
    </template>
  </modal>

  <div class="toast-container position-fixed top-0 end-0 p-2">
    <toast :id="'toastSuccess'" :type="'success'" :show="this.toasts.toastSuccess.show" @close-toast="this.closeToast">
      <template #body>
        <div v-html="this.toasts.toastSuccess.msg"></div>
      </template>
    </toast>

    <toast :id="'toastError'" :type="'danger'" :show="this.toasts.toastError.show" @close-toast="this.closeToast">
      <template #body>
        <div v-html="this.toasts.toastError.msg"></div>
      </template>
    </toast>
  </div>

  <!--<div class="row">
    <div class="col">
      <div class="card border-secondary" :class="this.loading === true ? 'block-grid' : ''">
        <div v-if="this.loading" class="overlay">
          <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1000">
            <div class="spinner-border text-primary" role="status"></div>
            <span class="txt-overlay">{{ this.translate.loading }}</span>
          </div>
        </div>
        <div class="card-header bg-secondary text-white">
          <span v-if="tag.id === null">
            {{ this.translate.formTitleCreate }}
          </span>
          <span v-else> {{ this.translate.formTitleUpdate }} #{{ this.tag.id }} </span>
        </div>
        <div class="card-body">
          <fieldset class="mb-3">
            <legend>
              {{ this.translate.colorTitle }}
            </legend>

            <p>{{ this.translate.colorDescription }}</p>

            <div class="input-group mb-3 me-3 float-end" style="width: auto">
              <button
                class="btn btn-secondary dropdown-toggle"
                type="button"
                data-bs-toggle="dropdown"
                aria-expanded="false"
              >
                {{ this.translate.linkColorChoice }}
              </button>
              <ul class="dropdown-menu">
                <li v-for="color in this.tabColor">
                  <a
                    class="dropdown-item no-control"
                    :style="'cursor:pointer;color:' + color"
                    @click="this.switchColor(color)"
                  >{{ color }}</a
                  >
                </li>
              </ul>
              <button @click="this.loadColorExemple()" class="btn btn-secondary" type="button">
                <i class="bi bi-arrow-clockwise"></i>
              </button>
            </div>

            <input
              type="color"
              @change="
                this.isErrorHexa = false;
                this.msgErrorExa = '';
              "
              class="form-control form-control-color float-start"
              id="tagColor"
              v-model="this.tag.color"
            />

            <input
              type="text"
              class="form-control"
              :class="this.msgErrorExa !== '' ? 'is-invalid' : ''"
              id="tagColorinput"
              v-model="this.tag.color"
              size="7"
              style="width: auto"
              @change="this.checkValideHex()"
              maxlength="7"
            />
            <div class="invalid-feedback">
              {{ this.msgErrorExa }}
            </div>
          </fieldset>

          <div v-for="key in this.locales.locales">
            <div v-for="translation in tag.tagTranslations">
              <div v-if="translation.locale === key">
                <div v-if="translation.locale === this.locales.current">
                  <div class="form-check form-switch float-end">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      role="switch"
                      id="flexSwitchCheckDefault"
                      v-model="this.autoCopy"
                    />
                    <label class="form-check-label" for="flexSwitchCheckDefault">{{ this.translate.autoCopy }}</label>
                  </div>

                  <h5 class="card-title">{{ this.translate.labelCurrent }}</h5>
                </div>
                <h5 v-else-if="this.locales.locales[1] === key" class="card-title">{{ this.translate.labelOther }}</h5>
                <div class="mb-3">
                  <label :for="'label-' + translation.locale" class="form-label"
                  >{{ this.translate.formInputLabelLabel }} {{ this.locales.localesTranslate[key] }}</label
                  >
                  <input
                    type="text"
                    :class="this.isNoEmptyInput(translation.id)"
                    class="form-control"
                    :id="'label-' + translation.locale"
                    placeholder=""
                    :disabled="this.isDisabled(translation.locale)"
                    v-model="translation.label"
                    v-on="
                      this.autoCopy && translation.locale === this.locales.current
                        ? { keyup: () => this.copyLabel(translation.label) }
                        : {}
                    "
                  />
                  <div class="invalid-feedback">
                    {{ this.translate.formInputLabelError }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <button class="btn btn-secondary" @click="this.save()" :disabled="this.canSubmit()">
            {{ this.getLabelSubmit() }}
          </button>
        </div>
      </div>
    </div>
    <div class="col-4">
      <div class="card border-secondary">
        <div class="card-header bg-secondary text-white">
          {{ this.translate.renduTitle }}
        </div>
        <div class="card-body">
          <div v-for="key in this.locales.locales">
            <div v-for="translation in tag.tagTranslations">
              <div v-if="translation.locale === key">
                <h5 v-if="translation.locale === this.locales.current" class="card-title">
                  {{ this.translate.labelCurrent }}
                </h5>
                <h5 v-else-if="this.locales.locales[1] === key" class="card-title mt-2 mb-2">
                  {{ this.translate.labelOther }}
                </h5>
                <b>{{ this.locales.localesTranslate[key] }}</b> :
                <span class="badge rounded-pill badge-nat" :style="'background-color: ' + tag.color">{{
                    translation.label
                  }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card border-secondary mt-3" v-if="tag.id !== null">
        <div class="card-header bg-secondary text-white">
          {{ this.translate.statTitle }}
        </div>
        <div class="card-body" v-html="this.templateStat"></div>
      </div>
    </div>
  </div> -->
</template>

<style scoped></style>
