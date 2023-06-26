<script>/**
 * Permet d'ajouter ou éditer un tag
 * @author Gourdon Aymeric
 * @version 1.0
 */

import axios from "axios";

export default {
  name: "TagForm",
  props: {
    url_form_tag: String,
    translate: [],
    locales: [],
    pTag: []
  },
  data() {
    return {
      loading: false,
      tag: this.pTag,
      tabColor: [],
      msgErrorExa: "",
      autoCopy: true,
      isErrorHexa: true,
      isErrorLabel: true,
    }
  },
  mounted() {
    this.loadColorExemple()
    if (this.tag.id !== null) {
      this.isErrorHexa = this.isErrorLabel = false;
    }
  },

  methods: {

    /**
     * sauvegarde des données
     */
    save() {

      this.loading = true;

      axios.post(this.url_form_tag, {}).then((response) => {

      }).catch((error) => {
        console.log(error);
      }).finally(() => {
        this.loading = false
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
        this.msgErrorExa = this.translate.formInputColorError
        this.isErrorHexa = true;
      }


    },

    /**
     * Affichage du label du bouton
     * @returns {*}
     */
    getLabelSubmit() {
      if (this.tag.id === null) {
        return this.translate.btnSubmitCreate;
      }
      return this.translate.btnSubmitUpdate;
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
      this.isErrorLabel = false;
      this.locales.locales.forEach((locale) => {
        this.tag.tagTranslations.forEach((translation) => {
          if (locale === translation.locale && translation.locale !== this.locales.current) {
            translation.label = label;
          }
        })
      })
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

      let css = "";
      this.isErrorLabel = false;
      this.tag.tagTranslations.forEach((translation) => {
        if (translation.id === translation_id && translation.label === "") {
          css = "is-invalid";
        }

        if (translation.label === "" || translation.label === null) {
          this.isErrorLabel = true;
        }
      })
      return css;
    },

    /**
     * Génère une valeur hexadécimale random
     * @returns {*|string}
     */
    generateRandomHexColor() {
      const randomColor = "#" + Math.floor(Math.random() * 16777215).toString(16);
      if (randomColor.length !== 7) {
        return this.generateRandomHexColor();
      } else {
        return randomColor;
      }
    },

    /**
     * Active ou désactive le bouton submit
     * @returns {boolean}
     */
    canSubmit() {
      return (this.isErrorHexa || this.isErrorLabel);
    }

  }
}

</script>

<template>

  <div :class="this.loading === true ? 'block-grid' : ''">
    <div v-if="this.loading" class="overlay">
      <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1000;">
        <div class="spinner-border text-primary" role="status"></div>
        <span class="txt-overlay">{{ this.translate.loading }}</span>
      </div>
    </div>

    <br/>
    <div class="row">
      <div class="col">

        <div class="card border-secondary">
          <div class="card-header bg-secondary text-white">
            <span v-if="tag.id === null">
              {{ this.translate.formTitleCreate }}
            </span>
            <span v-else>
              {{ this.translate.formTitleUpdate }} #{{ this.tag.id }}
            </span>
          </div>
          <div class="card-body">

            <fieldset class="mb-3">
              <legend>
                {{ this.translate.colorTitle }}
              </legend>

              <p>{{ this.translate.colorDescription }}</p>

              <div class="input-group mb-3 me-3 float-end" style="width: auto">
                <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">{{ this.translate.linkColorChoice }}</button>
                <ul class="dropdown-menu">
                  <li v-for="color in this.tabColor">
                    <a class="dropdown-item" :style="'cursor:pointer;color:' + color" @click="this.switchColor(color)">{{ color }}</a>
                  </li>
                </ul>
                <button @click="this.loadColorExemple()" class="btn btn-secondary" type="button">
                  <i class="bi bi-arrow-clockwise"></i></button>
              </div>

              <input type="color" @change="this.isErrorHexa = false; this.msgErrorExa = ''" class="form-control form-control-color float-start" id="tagColor" v-model="this.tag.color">

              <input type="text" class="form-control"
                  :class="this.msgErrorExa !== '' ? 'is-invalid' : ''"
                  id="tagColorinput"
                  v-model="this.tag.color"
                  size="7" style="width: auto"
                  @change="this.checkValideHex()" maxlength="7">
              <div class="invalid-feedback">
                {{ this.msgErrorExa }}
              </div>

            </fieldset>

            <div v-for="key in this.locales.locales">
              <div v-for="translation in tag.tagTranslations">
                <div v-if="translation.locale === key">

                  <div v-if="translation.locale === this.locales.current">

                    <div class="form-check form-switch float-end">
                      <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault" v-model="this.autoCopy">
                      <label class="form-check-label" for="flexSwitchCheckDefault">{{ this.translate.autoCopy }}</label>
                    </div>

                    <h5 class="card-title">{{ this.translate.labelCurrent }}</h5>

                  </div>
                  <h5 v-else-if="this.locales.locales[1] === key" class="card-title">{{ this.translate.labelOther }}</h5>
                  <div class="mb-3">
                    <label :for="'label-' + translation.locale" class="form-label">{{ this.translate.formInputLabelLabel }} {{ this.locales.localesTranslate[key] }}</label>
                    <input type="text"
                        :class="this.isNoEmptyInput(translation.id)"
                        class="form-control"
                        :id="'label-' + translation.locale"
                        placeholder=""
                        :disabled="this.isDisabled(translation.locale)"
                        v-model="translation.label"
                        v-on="this.autoCopy && translation.locale === this.locales.current ? { keyup: () => this.copyLabel(translation.label) } : {} "
                    >
                    <div class="invalid-feedback">
                      {{ this.translate.formInputLabelError }}
                    </div>
                  </div>


                </div>
              </div>
            </div>

            <button class="btn btn-secondary" :disabled="this.canSubmit()">{{ this.getLabelSubmit() }}</button>

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
                  <h5 v-if="translation.locale === this.locales.current" class="card-title">{{ this.translate.labelCurrent }}</h5>
                  <h5 v-else-if="this.locales.locales[1] === key" class="card-title mt-2 mb-2">{{ this.translate.labelOther }}</h5>
                  <b>{{ this.locales.localesTranslate[key] }}</b> :
                  <span class="badge rounded-pill badge-nat" :style="'background-color: ' + tag.color">{{ translation.label }}</span>

                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</template>

<style scoped>

</style>