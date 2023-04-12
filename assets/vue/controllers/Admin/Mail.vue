<script>

import MarkdownEditor from "../../Components/MarkdownEditor.vue";

export default {
    name: "Mail",
    components: {MarkdownEditor},
    data() {
        return {
            value: '*tto*',
            value2: '**2eme**',
            editorValue:""
        }
    },
    methods: {
        test(value)
        {
            this.editorValue = value;
        }
    }
}
</script>

<template>
  <markdown-editor
          :me-value="this.value"
          :me-rows="10"
          @editor-value="test"
  >
  </markdown-editor>

    <markdown-editor
            :me-value="this.value2"
            :me-rows="20"
            @editor-value="test"
    >
    </markdown-editor>

    <div class="border-primary border-1">
        {{ editorValue  }}
    </div>
</template>

<style scoped>

</style>