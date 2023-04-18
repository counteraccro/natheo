<script>

import MarkdownEditor from "../../Components/MarkdownEditor.vue";
import axios from "axios";
import {marked} from 'marked'

export default {
    name: "Mail",
    components: {MarkdownEditor},
    props: {
        url_data: String
    },
    data() {
        return {
            value: '*tto*',
            value2: '**2eme**',
            editorValue: "",
            translateEditor: {},
            translate: {},
            languages: {},
            currentLanguage: null,
            mail: [],
            loading: false,
            url_save: '',
            msgSuccess: '',
        }
    },
    mounted() {
        this.loadData();
    },

    methods: {

        /**
         * Récupère les données liées à la gestion des emails
         */
        loadData() {

            this.loading = true;

            axios.post(this.url_data, {
                'locale': this.currentLanguage
            }).then((response) => {
                this.translateEditor = response.data.translateEditor
                this.languages = response.data.languages;
                this.translate = response.data.translate;
                this.currentLanguage = response.data.locale;
                this.mail = response.data.mail;
                this.url_save = response.data.save_url;
            }).catch((error) => {
                console.log(error);
            }).finally(() => {
                this.loading = false
            });
        },

        /**
         * Event de choix de la langue
         * @param event
         */
        selectLanguage(event) {
            this.currentLanguage = event.target.value;
            if (this.currentLanguage !== "") {
                this.loadData();
            }
        },

        /**
         * Réinitialise le message de sauvegarde
         */
        removeMsg()
        {
            this.msgSuccess = '';
        },

        /**
         * Permet de sauvegarder le content
         * @param value
         */
        save(value) {

            this.loading = true;
            axios.post(this.url_save, {
                'locale': this.currentLanguage,
                'content': value
            }).then((response) => {
                this.msgSuccess = response.data.msg;
            }).catch((error) => {
                console.log(error);
            }).finally(() => {
                console.log('ici');
                setTimeout(this.removeMsg, 3000);
                this.loading = false
            });
        }
    }
}
</script>

<template>

    <div>
        <select class="form-select no-control" id="select-file" @change="selectLanguage($event)" v-model="this.currentLanguage">
            <option value="">{{ this.translate.listLanguage }}</option>
            <option v-for="(language, key) in this.languages" v-bind:value="key">{{ language }}</option>
        </select>
    </div>

    <div :class="this.loading === true ? 'block-grid' : ''">
        <div v-if="this.loading" class="overlay">
            <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1000;">
                <div class="spinner-border text-primary" role="status"></div>
                <span class="txt-overlay">{{ this.translate.loading }}</span>
            </div>
        </div>
        <div v-for="mail in this.mail">
            <div class="card mt-2">
                <div class="card-header text-bg-secondary">
                    <div class="mt-1 float-start">{{ mail.title }}</div>
                    <div class="btn btn-sm btn-secondary float-end"><i class="bi bi-send-check-fill"></i></div>
                </div>
                <div class="card-body">

                    <div v-if="msgSuccess !== ''" class="alert alert-success" v-html="'<i class=\'bi bi-check-circle-fill\'></i> ' + this.msgSuccess">
                    </div>

                    <p>{{ mail.description }}</p>
                    <markdown-editor :key="mail.key"
                            :me-value="mail.contentTrans"
                            :me-rows="10"
                            :me-translate="translateEditor"
                            :me-key-words="mail.keyWords"
                            @editor-value="save"
                    >
                    </markdown-editor>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>