<template>
  <div class="border border-1 border-light p-3">
    Ceci est un exemple d'appel en Vue.js <b>version {{ this.version }}</b> oki
    <hr/>
    Vous êtes connecté en tant que <b>{{ name }}</b>
    <div v-if="this.id!==''">
      Id du bouton {{this.id}} <br />
      data-name {{ this.dataName }}
    </div>
  </div>
</template>

<script>
import {version} from 'vue'

export default {
  props: {
    name: String,
  },
  data() {
    return {
      version: '',
      id: '',
      dataName: '',
    }
  },
  mounted() {
    document.getElementById('test-click-vue-js').addEventListener('click', this.alert_me)
    this.version = version;
  },
  methods: {
    alert_me(event) {
      this.id = event.target.getAttribute('data-id');
      this.dataName = event.target.getAttribute('data-name');
    }
  }
};
</script>