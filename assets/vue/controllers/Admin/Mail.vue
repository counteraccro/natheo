<script>

import MarkdownEditor from "../../Components/MarkdownEditor.vue";
import axios from "axios";
import {marked} from 'marked'
import { nextTick } from 'vue'

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
            mails: []
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

            axios.post(this.url_data, {
                'locale' : this.currentLanguage
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

    <div v-for="mail in this.mails">
        <div>{{ mail.title }}</div>
        <div>{{ mail.description }}</div>
        <markdown-editor :key="mail.key"
            :me-value="mail.contentTrans"
            :me-rows="10"
            :me-translate="translateEditor"
            @editor-value="test"
        >
        </markdown-editor>
    </div>




    <div class="border-primary border-1" v-html="editorValue">
    </div>
</template>

<style scoped>

</style>