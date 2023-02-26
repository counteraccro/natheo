<template>
  <div class="border border-1 border-light p-3">
    Ceci est un exemple d'appel en Vue.js <b>version {{version}}</b>
    <hr />
    Vous êtes connecté en tant que <b>{{name}}</b>
  </div>
</template>

<script setup>
import {version} from "vue";
defineProps({
  name: String
});
</script>