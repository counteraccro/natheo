<template>
  <div class="border border-1 border-light p-3">
    Ceci est un exemple d'appel en Vue.js <b>version {{version}}</b>
    <hr />
    Vous êtes connecté en tant que <b>{{name}}</b>
  </div>
</template>

<script setup>
import {version} from "vue";
import axios from 'axios'
import VueAxios from 'vue-axios'

defineProps({
  name: String,
  methods: {
    getList() {
      //this.axios.get()
    }
  }
});
</script>