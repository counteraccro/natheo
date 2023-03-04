<template>
  <div class="border border-1 border-light p-3">
    Ceci est un exemple d'appel en Vue.js <b>version {{ this.version }}</b> oki
    <hr />
    Vous êtes connecté en tant que <b>{{name}}</b>
  </div>
</template>

<script>
import axios from 'axios'
import VueAxios from 'vue-axios'
import { version } from 'vue'

export default{
  props : {
    name : String,
  },
  data() {
    return {
      version : ''
    }
  },
  mounted() {
    document.getElementById('test-click-vue-js').addEventListener('click', this.alert_me)
    this.version = version;
  },
  methods: {
    alert_me() {
      alert(this.version);
    }
  }
};
</script>