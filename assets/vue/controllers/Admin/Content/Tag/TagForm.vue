<script lang="ts">
/**
 * Permet d'ajouter ou éditer un tag
 * @author Gourdon Aymeric
 * @version 1.2
 */
import { defineComponent, type PropType } from 'vue';
import axios from 'axios';
import type { Tag, TagFormLocales, TagFormTranslate } from '@/ts/Tag/TagForm.type';
import { withCsrf } from '@/vue/Composables/Admin/csrf';
import { emitter } from '@/vue/Composables/Global/useEvent';
import SkeletonForm from '@/vue/Components/Skeleton/Form.vue';
import Modal from '@/vue/Components/Global/Modal.vue';
import { toastSuccess, toastError, toastRequestError } from '@/vue/Composables/Global/toast';

export default defineComponent({
  name: 'TagForm',
  components: { Modal, SkeletonForm },
  props: {
    url: { type: String, required: true },
    url_stats: { type: String, required: true },
    url_index: { type: String, required: true },
    url_update: { type: String, default: '' },
    url_delete: { type: String, default: '' },
    translate: { type: Object as PropType<TagFormTranslate>, required: true },
    locales: { type: Object as PropType<TagFormLocales>, required: true },
    pTag: { type: Object as PropType<Tag>, required: true },
    can_delete: { type: Boolean, default: false },
    csrf_token_save: { type: String, required: true },
    csrf_token_delete: { type: String, default: '' },
  },
  data() {
    return {
      loading: false,
      tag: this.pTag,
      tabColor: [] as string[],
      msgErrorExa: '',
      autoCopy: true,
      isErrorHexa: true,
      showErrors: false,
      templateStat: '',
      classNoControl: '',
      showModalConfirmDelete: false,
    };
  },
  mounted() {
    this.loadColorExemple();
    if (this.tag.id !== null) {
      this.isErrorHexa = false;
      this.autoCopy = false;
      this.loadStat();
    }
  },
  computed: {
    isErrorLabel(): boolean {
      if (!this.showErrors) {
        return false;
      }

      return this.tag.tagTranslations.some((translation) => translation.label === '' || translation.label === null);
    },
  },

  methods: {
    /**
     * Sauvegarde des données
     */
    save(): void {
      this.loading = true;
      this.classNoControl = 'no-control';

      axios
        .post(this.url, { tag: this.tag }, withCsrf(this.csrf_token_save))
        .then((response) => {
          if (response.data.success === false) {
            toastError(response.data.msg ?? this.translate.errorSave);
            return;
          }

          emitter.emit('reset-check-confirm');
          toastSuccess(this.translate.successSave);

          if (response.data.etat === 'new') {
            window.location.href = this.url_index;
          }
        })
        .catch((error) => {
          console.error(error);
          toastError(error.response?.data?.msg ?? this.translate.errorSave);
        })
        .finally(() => {
          this.loadStat();
          this.loading = false;
        });
    },

    /**
     * Charge le bloc de statistiques du tag
     */
    loadStat(): void {
      axios
        .get(this.url_stats)
        .then((response) => {
          this.templateStat = response.data;
        })
        .catch((error) => {
          toastRequestError(error);
        });
    },

    /**
     * Supprime un tag
     */
    ajaxDelete(): void {
      this.loading = true;
      this.showModalConfirmDelete = false;

      axios
        .delete(this.url_delete, withCsrf(this.csrf_token_delete))
        .then((response) => {
          if (response.data.success === true) {
            toastSuccess(response.data.msg);
            setTimeout(() => {
              window.location.href = this.url_index;
            }, 1500);
          } else {
            toastError(response.data.msg);
          }
        })
        .catch((error) => {
          console.error(error);
          toastError(error.response?.data?.msg ?? this.translate.errorSave);
        })
        .finally(() => {
          this.loading = false;
        });
    },

    /**
     * Charge une liste de 5 couleurs aléatoires
     */
    loadColorExemple(): void {
      this.tabColor = [];
      for (let i = 0; i < 5; i++) {
        this.tabColor.push(this.generateRandomHexColor());
      }
    },

    /**
     * Vérifie si la valeur hexadecimal est correcte
     */
    checkValideHex(): void {
      this.msgErrorExa = '';
      this.isErrorHexa = false;

      const reg = /^#[0-9a-f]{6}$/i;
      if (!reg.test(this.tag.color)) {
        this.msgErrorExa = this.translate.formInputColorError;
        this.isErrorHexa = true;
      }
    },

    /**
     * Affichage du label du bouton
     */
    getLabelSubmit(): string {
      if (this.tag.id === null) {
        const icon =
          '<svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> ';

        return icon + this.translate.btnSubmitCreate;
      }

      const icon =
        '<svg class="icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="2" d="M10.779 17.779 4.36 19.918 6.5 13.5m4.279 4.279 8.364-8.643a3.027 3.027 0 0 0-2.14-5.165 3.03 3.03 0 0 0-2.14.886L6.5 13.5m4.279 4.279L6.499 13.5m2.14 2.14 6.213-6.504M12.75 7.04 17 11.28"></path></svg>';
      return icon + this.translate.btnSubmitUpdate;
    },

    /**
     * Permet de changer la couler choisie
     * @param color
     */
    switchColor(color: string): void {
      this.isErrorHexa = false;
      this.msgErrorExa = '';
      this.tag.color = color;
    },

    /**
     * Duplique les labels en fonction de la valeur du label du current locale
     * @param label
     */
    copyLabel(label: string): void {
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
     */
    isDisabled(locale: string): boolean {
      return this.autoCopy && locale !== this.locales.current;
    },

    /**
     * Vérifie si le label n'est pas vide
     * @param translation_id
     */
    isNoEmptyInput(translation_id: number | null): string {
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
     */
    generateRandomHexColor(): string {
      const randomColor = '#' + Math.floor(Math.random() * 16777215).toString(16);
      if (randomColor.length !== 7) {
        return this.generateRandomHexColor();
      } else {
        return randomColor;
      }
    },

    toggleCopy(): void {
      this.autoCopy = !this.autoCopy;
    },

    /**
     * Active ou désactive le bouton submit
     */
    canSubmit(): boolean {
      return this.isErrorHexa || this.isErrorLabel;
    },

    /**
     * Ferme la modale
     */
    hideModal(): void {
      this.showModalConfirmDelete = false;
    },
  },
});
</script>

<template>
  <div v-if="loading">
    <SkeletonForm />
  </div>

  <div v-else>
    <div class="card mb-4">
      <div class="card-header">
        <div>
          <div class="card-title">
            <svg
              class="card-icon"
              aria-hidden="true"
              xmlns="http://www.w3.org/2000/svg"
              width="24"
              height="24"
              fill="none"
              viewBox="0 0 24 24"
              style="color: var(--primary)"
            >
              <path
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M18.045 3.007 12.31 3a1.965 1.965 0 0 0-1.4.585l-7.33 7.394a2 2 0 0 0 0 2.805l6.573 6.631a1.957 1.957 0 0 0 1.4.585 1.965 1.965 0 0 0 1.4-.585l7.409-7.477A2 2 0 0 0 21 11.479v-5.5a2.972 2.972 0 0 0-2.955-2.972Zm-2.452 6.438a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z"
              />
            </svg>
            {{ translate.styleTitle }}
          </div>
          <p class="card-subtitle">
            {{ translate.styleDescription }}
          </p>
        </div>
      </div>
      <div class="p-5">
        <div class="flex justify-between gap-10">
          <div class="form-group w-7/12">
            <label for="tagColor" class="form-label">{{ translate.colorTitle }} </label>
            <div class="flex items-center gap-3">
              <input
                type="color"
                @change="
                  isErrorHexa = false;
                  msgErrorExa = '';
                "
                class="form-color"
                id="tagColor"
                v-model="tag.color"
              />

              <input
                type="text"
                class="form-input flex-1"
                :class="msgErrorExa !== '' ? 'is-invalid' : ''"
                id="tagColorinput"
                v-model="tag.color"
                size="7"
                style="width: auto"
                @change="checkValideHex()"
                maxlength="7"
              />
            </div>
            <span v-if="msgErrorExa" class="form-text text-error">✗ {{ msgErrorExa }}</span>
            <span v-else class="form-text">{{ translate.formInputColorHelp }}</span>
          </div>

          <div class="w-3/12">
            <label for="tagColor" class="form-label">{{ translate.renduTitle }} </label>
            <div v-for="key in locales.locales">
              <div v-for="translation in tag.tagTranslations">
                <div v-if="translation.locale === key" class="text-fg text-sm mb-1">
                  {{ locales.localesTranslate[key] }} :
                  <span class="badge rounded-pill badge-nat" :style="'background-color: ' + tag.color">{{
                    translation.label
                  }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-header">
        <div>
          <div class="card-title">
            <svg
              class="card-icon"
              aria-hidden="true"
              xmlns="http://www.w3.org/2000/svg"
              width="24"
              height="24"
              fill="none"
              viewBox="0 0 24 24"
              style="color: var(--primary)"
            >
              <path
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M10 11h2v5m-2 0h4m-2.592-8.5h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
              />
            </svg>
            {{ translate.textTitle }}
          </div>
          <p class="card-subtitle">
            {{ translate.textDescription }}
          </p>
        </div>
      </div>
      <div class="p-5">
        <div
          v-for="key in locales.locales"
          :class="key === locales.current ? 'mb-2' : 'mb-5'"
          class="last:mb-0 flow-root"
        >
          <div v-for="translation in tag.tagTranslations">
            <div v-if="translation.locale === key">
              <div class="form-group">
                <label :for="'label-' + translation.locale" class="form-label"
                  >{{ translate.formInputLabelLabel }} {{ locales.localesTranslate[key] }}
                </label>
                <input
                  type="text"
                  :class="isNoEmptyInput(translation.id)"
                  class="form-input"
                  :id="'label-' + translation.locale"
                  :placeholder="translate.formInputLabelPlaceholder"
                  @blur="!translation.label ? (showErrors = true) : ''"
                  :disabled="isDisabled(translation.locale)"
                  v-model="translation.label"
                  v-on="
                    autoCopy && translation.locale === locales.current
                      ? { keyup: () => copyLabel(translation.label ?? '') }
                      : {}
                  "
                />

                <div class="form-switch float-end mt-2 mb-0!" v-if="translation.locale === locales.current">
                  <input
                    type="checkbox"
                    class="switch-input"
                    id="switch-basic"
                    role="switch"
                    :checked="autoCopy ? true : false"
                  />
                  <label class="switch-toggle" @click="toggleCopy()"></label>
                  <span class="switch-label" @click="toggleCopy()"
                    ><span
                      class="switch-label-text"
                      style="font-weight: 400; font-size: 0.8125rem; color: var(--text-secondary)"
                      >{{ translate.autoCopy }}</span
                    ></span
                  >
                </div>
                <span
                  v-if="isErrorLabel && (translation.locale === locales.current || !autoCopy)"
                  class="form-text text-error"
                  >✗
                  {{ translate.formInputLabelError }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 pt-4 mt-5">
          <button
            v-if="tag.id !== null && can_delete"
            type="button"
            class="btn btn-sm btn-danger"
            @click="showModalConfirmDelete = true"
          >
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
              ></path>
            </svg>
            {{ translate.btnDelete }}
          </button>
          <div class="flex flex-wrap gap-2 ml-auto">
            <button type="button" class="btn btn-outline-dark btn-sm" onclick="window.history.back()">
              <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>

              {{ translate.btnCancel }}
            </button>
            <button
              class="btn btn-sm btn-primary"
              @click="save()"
              :disabled="canSubmit()"
              v-html="getLabelSubmit()"
            ></button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="tag.id !== null && templateStat !== ''" class="card mb-4">
      <div class="card-header">
        <div>
          <div class="card-title">
            <svg
              class="card-icon"
              aria-hidden="true"
              xmlns="http://www.w3.org/2000/svg"
              width="24"
              height="24"
              fill="none"
              viewBox="0 0 24 24"
              style="color: var(--primary)"
            >
              <path
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 4v15a1 1 0 0 0 1 1h15M8 16l2.5-5.5 3 3L17.273 7 20 9.667"
              />
            </svg>

            {{ translate.statTitle }}
          </div>
        </div>
      </div>
      <div class="p-5">
        <div class="form-text" v-html="templateStat"></div>
      </div>
    </div>
  </div>

  <modal
    :id="'modal-confirm-delete'"
    :show="showModalConfirmDelete"
    @close-modal="hideModal"
    :option-show-close-btn="false"
  >
    <template #title>
      <svg
        class="inline-block w-5 h-5 align-text-bottom"
        aria-hidden="true"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
        />
      </svg>
      {{ translate.modaleConfirmDeleteTitle }}
    </template>
    <template #body>
      <div v-html="translate.modaleConfirmDeleteMessage"></div>
    </template>
    <template #footer>
      <button type="button" class="btn btn-outline-dark btn-sm" @click="hideModal()">
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
      <button type="button" class="btn btn-danger btn-sm" @click="ajaxDelete">
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
    </template>
  </modal>
</template>

<style scoped></style>
