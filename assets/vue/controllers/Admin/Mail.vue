<script>

import MarkdownEditor from "../../Components/MarkdownEditor.vue";

export default {
    name: "Mail",
    components: {MarkdownEditor},
    data() {
        return {
            value: '*tto*',
            value2: '**2eme**'
        }
    }
}
</script>

<template>
  <markdown-editor
          :me-value="this.value"
          :me-rows="10"
  >
  </markdown-editor>

    <markdown-editor
            :me-value="this.value2"
            :me-rows="20"
    >
    </markdown-editor>
</template>

<style scoped>

</style>