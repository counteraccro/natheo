<script>

import MarkdownEditor from "../../Components/MarkdownEditor.vue";
import axios from "axios";

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
            translateEditor: {}
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
            axios.post(this.url_data).then((response) => {
                this.translateEditor = response.data.translateEditor
            }).catch((error) => {
                console.log(error);
            }).finally();
        },

        test(value) {
            this.editorValue = value;
        }
    }
}
</script>

<template>
    <markdown-editor
            :me-value="this.value"
            :me-rows="10"
            v-bind:me-translate="translateEditor"
            @editor-value="test"
    >
    </markdown-editor>

    <markdown-editor
            :me-value="this.value2"
            :me-rows="20"
            :me-translate="translateEditor"
            @editor-value="test"
    >
    </markdown-editor>

    <div class="border-primary border-1">
        {{ editorValue }}
    </div>
</template>

<style scoped>

</style>