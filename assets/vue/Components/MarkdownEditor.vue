<script>

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Editeur Markdown
 */

import {marked} from 'marked'
import {debounce} from 'lodash-es'
import {Tooltip} from 'bootstrap/dist/js/bootstrap.esm.min.js'

export default {
    name: "MarkdownEditor",
    props: {
        meValue: String,
        meRows: Number
    },
    data() {
        return {
            value: this.meValue,
            id: ""
        }
    },
    mounted() {
        this.id = this.randomIntFromInterval(1, 9) + '' + this.randomIntFromInterval(1, 9);
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new Tooltip(tooltipTriggerEl))
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

        addTable()
        {
            let balise = '| Column 1 | Column 2 | Column 3 |\n' +
                '| -------- | -------- | -------- |\n' +
                '| Text     | Text     | Text     |';
            this.addElement(balise, 0, false);
        },

        addCode()
        {
            let balise = '```\n' +
                '\n' +
                '```';
            this.addElement(balise, 4, false);
        },

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
        }
    }
}
</script>

<template>
    <div class="editor">
        <div class="header mb-2">
            <div class="btn btn-secondary btn-sm me-1" @click="this.addElement('****', '2', true)" data-bs-toggle="tooltip" data-bs-title="Default tooltip">
                <i class="bi bi-type-bold"></i></div>
            <div class="btn btn-secondary btn-sm me-1" @click="this.addElement('**', '1', true)">
                <i class="bi bi-type-italic"></i></div>
            <div class="btn btn-secondary btn-sm me-1" @click="this.addElement('~~~~', '2', true)">
                <i class="bi bi-type-strikethrough"></i></div>
            <div class="btn btn-secondary btn-sm me-1" @click="this.addElement('> ', '0', false)">
                <i class="bi bi-quote"></i></div>
            <div class="btn btn-secondary btn-sm me-1" @click="this.addElement('- ', '0', false)">
                <i class="bi bi-list-ul"></i></div>
            <div class="btn btn-secondary btn-sm me-1" @click="this.addElement('1. ', '0', false)">
                <i class="bi bi-list-ol"></i></div>
            <div class="btn btn-secondary btn-sm me-1" @click="this.addTable">
                <i class="bi bi-table"></i></div>
            <div class="btn btn-secondary btn-sm me-1" @click="this.addTable">
                <i class="bi bi-link"></i></div>
            <div class="btn btn-secondary btn-sm me-1" @click="this.addTable">
                <i class="bi bi-image"></i></div>
            <div class="btn btn-secondary btn-sm me-1" @click="this.addCode">
                <i class="bi bi-code"></i></div>
        </div>

        <textarea :id="'editor-'+ this.id" class="form-control" :value="this.value" @input="update" :rows="this.meRows"></textarea>
        <div :id="'emailHelp-' + this.id" class="form-text">We'll never share your email with anyone else.</div>

        <div>Prévisualisation</div>
        <div class="output" v-html="output"></div>
    </div>
</template>

<style scoped>

</style>