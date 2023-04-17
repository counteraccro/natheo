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
            mails: [],
            loading: false,
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
                this.mails = response.data.mails;

                console.log('Chargement donnée');
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

        test(value) {
            this.editorValue = marked(value);
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
        <div v-for="mail in this.mails">
            <div class="card mt-2">
                <div class="card-header text-bg-secondary">
                    <div class="mt-1 float-start">{{ mail.title }}</div>
                    <div class="btn btn-sm btn-secondary float-end"><i class="bi bi-send-check-fill"></i></div>
                </div>
                <div class="card-body">
                    <p>{{ mail.description }}</p>
                    <markdown-editor :key="mail.key"
                            :me-value="mail.contentTrans"
                            :me-rows="10"
                            :me-translate="translateEditor"
                            :me-key-words="mail.keyWords"
                            @editor-value="test"
                    >
                    </markdown-editor>
                </div>
            </div>
        </div>
    </div>


    <div class="border-primary border-1" v-html="editorValue">
    </div>
</template>

<style scoped>

</style>