<script>

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Editeur Markdown
 */

import {marked} from 'marked'
import {debounce} from 'lodash-es'
import {Modal} from 'bootstrap'

export default {
    name: "MarkdownEditor",
    props: {
        meValue: String,
        meRows: Number,
        meTranslate : Object,
        meKeyWords: [],
        meSave: Boolean
    },
    emits: ['editor-value', 'editor-value-change'],
    data() {
        return {
            value: this.meValue,
            id: "",
            modal: "",
            titleModal: "",
            linkModal: "",
            textModal: "",
            linkLabelModal: "",
            isImage: false,
            isValide: "",
        }
    },
    mounted() {
        this.id = this.randomIntFromInterval(1, 9) + '' + this.randomIntFromInterval(1, 9);
        this.modal = new Modal(document.getElementById("modal-markdown-editor"), {});
    },
    computed: {
        output() {
            return marked(this.value)
        }
    },
    methods: {
        update: debounce(function (e) {
            this.value = e.target.value
        }, 100),

        randomIntFromInterval(min, max) {
            return Math.floor(Math.random() * (max - min + 1) + min)
        },

        /**
         * Ajoute la balise table
         */
        addTable()
        {
            let balise = '| Column 1 | Column 2 | Column 3 |\n' +
                '| -------- | -------- | -------- |\n' +
                '| Text     | Text     | Text     |';
            this.addElement(balise, 0, false);
        },

        /**
         * Ajoute la balise code
         */
        addCode()
        {
            let balise = '```\n' +
                '\n' +
                '```';
            this.addElement(balise, 4, false);
        },

        /**
         * Ajoute un lien ou une image
         * @param modal
         * @param image
         */
        addLink(modal, image)
        {
            if(modal)
            {
                this.linkModal = 'https://';
                if(image)
                {
                    this.titleModal = this.meTranslate.modalTitreImage;
                    this.linkLabelModal = this.meTranslate.modalInputUrlImage;
                }
                else {
                    this.titleModal = this.meTranslate.modalTitreLink;
                    this.linkLabelModal = this.meTranslate.modalInputUrlLink;
                }

                this.isImage = image;
                this.modal.show();
            }
            else {
                let balise = '';
                if(image)
                {
                    balise = '![' + this.textModal + '](' + this.linkModal + ')';
                }
                else {
                    balise = '[' + this.textModal + '](' + this.linkModal + ')';
                }
                this.addElement(balise, 0, false);
                this.textModal = this.linkModal = '';
                this.modal.hide();
            }

            return false;
        },

        closeModal()
        {
            this.modal.hide();
        },

        /**
         * Ajoute un élément dans l'input
         * @param balise
         * @param position
         * @param separate
         * @returns {boolean}
         */
        addElement(balise, position, separate) {

            let input = document.getElementById("editor-" + this.id);
            let start = input.selectionStart;
            let end = input.selectionEnd;
            let value = this.value;

            let select = window.getSelection().toString();

            if (select === '') {
                this.value = value.slice(0, start) + balise + value.slice(end);
                input.value = this.value;
                let caretPos = start + balise.length;
                input.focus();
                input.setSelectionRange(caretPos - position, caretPos - position);

            } else {

                let before = value.slice(0, start);
                let after = value.slice(end);
                let replace = '';

                if (separate) {
                    let b = balise.slice(balise.length / 2);
                    replace = b + select.toString() + b;
                } else {
                    replace = balise + select.toString()
                }

                this.value = before + replace + after;
                input.value = this.value;

                let caretPos = start + replace.length;
                input.focus();
                input.setSelectionRange(caretPos, caretPos);

            }
            return false;
        },

        isValideInput(event)
        {
            this.isValide = '';
            let value = event.target.value;
            if(value === "")
            {
                this.isValide = 'is-invalid';
            }
            this.$emit('editor-value-change', value);
        }
    }
}
</script>

<template>

    <div class="modal fade" id="modal-markdown-editor" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-secondary">
                    <h1 class="modal-title fs-5 text-white"><i class="bi bi-plus-circle-fill"></i> {{ this.titleModal }}</h1>
                    <button type="button" class="btn-close" @click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <div>
                        <div class="mb-3">
                            <label for="link-modal" class="form-label">{{ this.linkLabelModal }}</label>
                            <input type="text" class="form-control" id="link-modal" placeholder="" v-model="linkModal">
                        </div>
                        <div class="mb-3">
                            <label for="text-modal" class="form-label">Texte</label>
                            <input type="text" class="form-control" id="text-modal" placeholder="" v-model="textModal">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" @click="addLink(false, this.isImage)"><i class="bi bi-check2-circle"></i> {{ this.meTranslate.modalBtnValide }}</button>
                    <button type="button" class="btn btn-secondary" @click="closeModal"><i class="bi bi-x-circle"></i> {{ this.meTranslate.modalBtnClose }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="editor">
        <div class="header mb-2">
            <div class="btn btn-secondary btn-sm me-1" @click="this.addElement('****', '2', true)"  :title="this.meTranslate.btnBold">
                <i class="bi bi-type-bold"></i></div>
            <div class="btn btn-secondary btn-sm me-1" @click="this.addElement('**', '1', true)" :title="this.meTranslate.btnItalic">
                <i class="bi bi-type-italic"></i></div>
            <div class="btn btn-secondary btn-sm me-1" @click="this.addElement('~~~~', '2', true)" :title="this.meTranslate.btnStrike">
                <i class="bi bi-type-strikethrough"></i></div>
            <div class="btn btn-secondary btn-sm me-1" @click="this.addElement('> ', '0', false)" :title="this.meTranslate.btnQuote">
                <i class="bi bi-quote"></i></div>
            <div class="btn btn-secondary btn-sm me-1" @click="this.addElement('- ', '0', false)" :title="this.meTranslate.btnList">
                <i class="bi bi-list-ul"></i></div>
            <div class="btn btn-secondary btn-sm me-1" @click="this.addElement('1. ', '0', false)" :title="this.meTranslate.btnListNumber">
                <i class="bi bi-list-ol"></i></div>
            <div class="btn btn-secondary btn-sm me-1" @click="this.addTable" :title="this.meTranslate.btnTable">
                <i class="bi bi-table"></i></div>
            <div class="btn btn-secondary btn-sm me-1" @click="this.addLink(true, false)" :title="this.meTranslate.btnLink">
                <i class="bi bi-link"></i></div>
            <div class="btn btn-secondary btn-sm me-1" @click="this.addLink(true, true)" :title="this.meTranslate.btnImage">
                <i class="bi bi-image"></i></div>
            <div class="btn btn-secondary btn-sm me-1" @click="this.addCode" :title="this.meTranslate.btnCode">
                <i class="bi bi-code"></i></div>
            <div class="dropdown float-start me-1">
                <button class="btn btn-secondary btn-sm dropdown-toggle" :title="this.meTranslate.titreLabel" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                   <i class="bi bi-type-h1"></i>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" style="cursor: pointer" @click="this.addElement('# ', '0', false)"> {{ this.meTranslate.titreH1 }}</a></li>
                    <li><a class="dropdown-item" style="cursor: pointer" @click="this.addElement('## ', '0', false)"> {{ this.meTranslate.titreH2 }}</a></li>
                    <li><a class="dropdown-item" style="cursor: pointer" @click="this.addElement('### ', '0', false)"> {{ this.meTranslate.titreH3 }}</a></li>
                    <li><a class="dropdown-item" style="cursor: pointer" @click="this.addElement('#### ', '0', false)"> {{ this.meTranslate.titreH4 }}</a></li>
                    <li><a class="dropdown-item" style="cursor: pointer" @click="this.addElement('##### ', '0', false)"> {{ this.meTranslate.titreH5 }}</a></li>
                    <li><a class="dropdown-item" style="cursor: pointer" @click="this.addElement('###### ', '0', false)"> {{ this.meTranslate.titreH6 }}</a></li>
                </ul>
            </div>
            <div class="dropdown float-start me-1" v-if="this.meKeyWords.length !== 0">
                <button class="btn btn-secondary btn-sm dropdown-toggle" :title="this.meTranslate.btnKeyWord" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    {{ this.meTranslate.btnKeyWord }} <i class="bi bi-key-fill"></i>
                </button>
                <ul class="dropdown-menu">
                    <li v-for="(label, key) in this.meKeyWords">
                        <a class="dropdown-item" style="cursor: pointer" @click="this.addElement('[[' + key + ']]', '0', false)"> {{ label }}</a>
                    </li>
                </ul>
            </div>
            <div v-if="this.meSave" class="btn btn-secondary btn-sm me-1 float-end" @click="$emit('editor-value', this.value)" :title="this.meTranslate.btnSave">
                <i class="bi bi-save"></i></div>
        </div>

        <textarea :id="'editor-'+ this.id" class="form-control" :class="this.isValide" :value="this.value" @input="update" :rows="this.meRows" @change="this.isValideInput"></textarea>
        <div class="invalid-feedback">
            {{ this.meTranslate.msgEmptyContent }}
        </div>
        <div :id="'emailHelp-' + this.id" class="form-text" v-html="this.meTranslate.help"></div>

        <fieldset class="mt-3">
            <legend>{{ this.meTranslate.render }}</legend>
            <div class="markdown-editor-output" v-html="output"></div>
        </fieldset>
    </div>
</template>

<style scoped>

</style>