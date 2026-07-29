<script>
/**
 * Permet d'afficher le listing des pages en preview
 * @author Gourdon Aymeric
 * @version 1.0
 */

export default {
  name: 'PreviewListing',
  props: {
    pContent: Object,
  },
  emits: [],
  data() {
    return {};
  },
  methods: {
    getDate(time) {
      let date = new Date(time * 1000);
      return date.toLocaleString();
    },
  },
};
</script>

<template>
  <ul>
    <li v-for="page in this.pContent.content.pages">
      <div>
        <a :href="page.slug"> {{ page.title }}</a>
        <span class="float-end"> {{ this.getDate(page.update) }} par {{ page.author }} </span>
      </div>
    </li>
  </ul>
</template>
