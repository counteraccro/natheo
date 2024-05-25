<script>

import {isEmpty} from "lodash-es";

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Affichage de la navBar des médias
 */
export default {
  name: 'MediasBreadcrum',
  props: {
    paths: Object,
  },
  emits: ['load-folder'],
  data() {
    return {}
  },
  computed: {},
  methods: {isEmpty}

}
</script>

<style>

</style>

<template>

  <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb" id="mediaBreadcrumb">
    <ol class="breadcrumb mb-0">
      <li class="breadcrumb-item text-white" aria-current="page">
        <i class="bi bi-house-fill"></i>&nbsp;
        <span v-if="isEmpty(this.paths)">Home</span>
        <a v-else class="text-white" style="cursor: pointer" @click="$emit('load-folder', 0)">Home</a>
      </li>
      <li v-for="item in this.paths" class="breadcrumb-item text-white" :class="this.paths.indexOf(item) === Object.keys(this.paths).length-1 ? 'active' : ''">
        <a v-if="this.paths.indexOf(item) !== Object.keys(this.paths).length-1" class="text-white" style="cursor: pointer" @click="$emit('load-folder', item.id)">{{ item.name }}</a>
        <span v-else>{{ item.name }}</span>
      </li>
    </ol>
  </nav>
</template>