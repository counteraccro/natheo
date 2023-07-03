<script>

import MarkdownEditor from "../../../Components/MarkdownEditor.vue";
import axios from "axios";

export default {
    name: "Mail",
    components: {MarkdownEditor},
    props: {
        url_data: String
    },
    data() {
        return {
            translateEditor: {},
            translate: {},
            languages: {},
            currentLanguage: null,
            mail: [],
            loading: false,
            url_save: '',
            url_demo: '',
            msgSuccess: '',
            isValideTitle: '',
            content: '',
            title: '',
            canSave: true
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
                this.url_demo = response.data.demo_url;
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
        removeMsg() {
            this.msgSuccess = '';
        },

        /**
         * Vérifie si on peut sauvegarder ou non
         */
        checkCanSave() {
            this.canSave = this.content !== '' && this.title !== '';
        },

        /**
         * Vérifie si le titre a été saisi ou non
         * @param event
         */
        checkTitle(event) {
            this.isValideTitle = '';
            let value = event.target.value;
            if (value === "") {
                this.isValideTitle = 'is-invalid';
            }
            this.checkCanSave();
        },

        /**
         * Event venant de markdownEditor pour récupérer la valeur saisie
         * @param value
         */
        saveContent(value) {
            this.content = value;
            this.checkCanSave();
        },

        /**
         * Mis à jour du titre et contenu
         * @param title
         * @param content
         */
        updateTitleContent(title, content)
        {
            this.content = content;
            this.title = title;
        },

        /**
         * Permet de sauvegarder le content
         */
        save() {
            this.loading = true;
            axios.post(this.url_save, {
                'locale': this.currentLanguage,
                'content': this.content,
                'title': this.title
            }).then((response) => {
                this.msgSuccess = response.data.msg;
            }).catch((error) => {
                console.log(error);
            }).finally(() => {
                setTimeout(this.removeMsg, 3000);
                this.loading = false
            });
        },

        /**
         * Permet d'envoyer un email de démo
         */
        sendDemoMail() {
            this.loading = true;
            axios.post(this.url_demo, {
            }).then((response) => {
                this.msgSuccess = response.data.msg;
            }).catch((error) => {
                console.log(error);
            }).finally(() => {
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
            {{ this.updateTitleContent(mail.titleTrans, mail.contentTrans) }}
            <div class="card mt-2">
                <div class="card-header text-bg-secondary">
                    <div class="mt-1 float-start">{{ mail.title }}</div>

                    <div class="btn btn-sm btn-success float-end" :class="!this.canSave ? 'disabled' : ''" @click="this.save">
                        <i class="bi bi-save-fill"></i></div>
                    <div class="btn btn-sm btn-secondary float-end" @click="this.sendDemoMail"><i class="bi bi-send-check-fill"></i></div>
                </div>
                <div class="card-body">

                    <div v-if="msgSuccess !== ''" class="alert alert-success" v-html="'<i class=\'bi bi-check-circle-fill\'></i> ' + this.msgSuccess">
                    </div>
                    <p>{{ mail.description }}</p>

                    <div class="mb-3">
                        <label for="titleTrans" class="form-label">{{ this.translate.titleTrans }}</label>
                        <input type="text" class="form-control no-control" :class="this.isValideTitle" id="titleTrans" v-model="mail.titleTrans" @change="this.checkTitle">
                        <div id="titleTransError" class="invalid-feedback">
                            {{ this.translate.msgEmptyTitle }}
                        </div>
                    </div>

                    <markdown-editor :key="mail.key"
                            :me-value="mail.contentTrans"
                            :me-rows="10"
                            :me-translate="translateEditor"
                            :me-key-words="mail.keyWords"
                            :me-save="false"
                            @editor-value=""
                            @editor-value-change="saveContent"
                    >
                    </markdown-editor>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>