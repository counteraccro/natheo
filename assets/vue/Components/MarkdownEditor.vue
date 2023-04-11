<script>

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Editeur Markdown
 */

import {marked} from 'marked'
import {debounce} from 'lodash-es'

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

        bold() {
            this.addElement('****', 2, true);
        },

        addElement(balise, position, separate) {

            let input = document.getElementById("editor-" + this.id);
            let start = input.selectionStart;
            let end = input.selectionEnd;
            let value = this.value;

            let select = window.getSelection().toString();

            if (select === '') {
                this.value = value.slice(0, start) + balise + value.slice(end);
                input.value  = this.value;
                let caretPos = start + balise.length;
                input.focus();
                input.setSelectionRange(caretPos-position, caretPos-position);

            } else {
                if(separate)
                {
                    let b = balise.slice(balise.length/2);
                    let replace = b + select + b;
                    this.value = this.value.replace(select, replace);
                    input.value  = this.value;
                    let caretPos = start + replace.length;
                    input.focus();
                    input.setSelectionRange(caretPos, caretPos);
                }

            }

            return false;

        }
    }
}
</script>

<template>
    <div class="editor">
        <div class="header">
            <div class="btn btn-secondary btn-sm" @click="this.addElement('****', '2', true)"><i class="bi bi-type-bold"></i></div>
        </div>

        <textarea :id="'editor-'+ this.id" class="form-control" :value="this.value" @input="update" :rows="this.meRows"></textarea>
        <div :id="'emailHelp-' + this.id" class="form-text">We'll never share your email with anyone else.</div>

        <div class="output" v-html="output"></div>
    </div>
</template>

<style scoped>

</style>