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
    }
  },
  mounted() {
    this.loadColorExemple()
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

      let reg = /^#([0-9a-f]{3}){1,2}$/i;
      if (!reg.test(this.tag.color)) {
        this.msgErrorExa = this.translate.formInputColorError
      }

    },

    /**
     * Permet de changer la couler choisie
     * @param color
     */
    switchColor(color) {
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
        })
      })
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

              <input type="color" class="form-control form-control-color float-start" id="tagColor" v-model="this.tag.color">

              <input type="text" class="form-control float-start"
                  :class="this.msgErrorExa !== '' ? 'is-invalid' : ''"
                  id="tagColorinput"
                  v-model="this.tag.color"
                  size="7" style="width: auto"
                  @change="this.checkValideHex()" maxlength="7">
              <div class="invalid-feedback">
                {{ this.msgErrorExa }}
              </div>

              <div class="input-group mb-3 me-3 float-end" style="width: auto">
                <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">{{ this.translate.linkColorChoice }}</button>
                <ul class="dropdown-menu">
                  <li v-for="color in this.tabColor">
                    <a class="dropdown-item" :style="'color:' + color" href="#" @click="this.switchColor(color)">{{ color }}</a>
                  </li>
                </ul>
                <button @click="this.loadColorExemple()" class="btn btn-secondary" type="button">
                  <i class="bi bi-arrow-clockwise"></i></button>
              </div>
            </fieldset>

            ici : {{ this.locales.locales[1] }}

            <div v-for="key in this.locales.locales">
              <div v-for="translation in tag.tagTranslations">
                <div v-if="translation.locale === key">

                  <div v-if="translation.locale === this.locales.current">

                    <div class="form-check form-switch float-end">
                      <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault" v-model="this.autoCopy">
                      <label class="form-check-label" for="flexSwitchCheckDefault">{{ this.translate.autoCopy }}</label>
                    </div>

                    <h4 class="card-title">{{ this.translate.labelCurrent }}</h4>

                  </div>
                  <h4 v-else-if="this.locales.locales[1] === key" class="card-title">{{ this.translate.labelOther }}</h4>
                  <div class="mb-3">
                    <label :for="'label-' + translation.locale" class="form-label">{{ this.translate.formInputLabelLabel }} {{ this.locales.localesTranslate[key] }}</label>
                    <input type="text"
                        class="form-control" :id="'label-' + translation.locale"
                        placeholder=""
                        v-model="translation.label"
                        v-on="this.autoCopy && translation.locale === this.locales.current ? { keyup: () => this.copyLabel(translation.label) } : {} "
                    >
                  </div>

                </div>
              </div>
            </div>

          </div>
        </div>


      </div>
      <div class="col-4">
        <div class="card border-secondary">
          <div class="card-header bg-secondary text-white">
            Featured
          </div>
          <div class="card-body">
            <div v-for="key in this.locales.locales">
              <div v-for="translation in tag.tagTranslations">
                <div v-if="translation.locale === key">
                  <h5 v-if="translation.locale === this.locales.current" class="card-title">{{ this.translate.labelCurrent }}</h5>
                  <h5 v-else-if="this.locales.locales[1] === key" class="card-title mt-2 mb-2">{{ this.translate.labelOther }}</h5>
                  <b>{{ this.locales.localesTranslate[key] }}</b> : <span class="badge rounded-pill badge-nat" :style="'background-color: ' + tag.color">{{ translation.label }}</span>

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