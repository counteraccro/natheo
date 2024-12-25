<script>/**
 * Permet d'afficher la preview des menus left et right
 * @author Gourdon Aymeric
 * @version 1.0
 */


export default {
  name: 'PreviewMenuLeftRight',
  props: {
    pMenu: Object,
    data: Object,
  },
  emits: [],
  data() {
    return {}
  },
  methods: {

  }

}
</script>

<template>
  <div>
    <a :href="this.data.url" class="d-flex align-items-center pb-3 mb-3 link-body-emphasis text-decoration-none border-bottom">
      <i class="bi pe-none me-2 h4 mt-2" :class="this.data.logo"></i>
      <span class="fs-5 fw-semibold">{{ this.data.name }}</span>
    </a>

    <ul v-if="this.pMenu.type === 11" class="list-unstyled ps-0">
        <li>Type 11</li>
    </ul>

    <ul v-if="this.pMenu.type === 12" class="list-unstyled ps-0">
      <li>Type 12</li>
    </ul>
  </div>
</template>