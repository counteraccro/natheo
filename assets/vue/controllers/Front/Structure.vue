<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Structure de la page de front
 */
export default {
  name: 'Structure',
  props: {
    slug: String,
    urls: Array,
  },
  emits: [],
  data() {
    return {
      value: 'Value',
    }
  },
  mounted() {

  },

  methods: {

  }
}
</script>

<template>

  <header>
    Header
  </header>
  <nav class="h-10 rounded bg-gray-300">
    Nav
  </nav>
  <main>
    <div class="grid grid-cols-12 gap-2 mt-2">
      <div class="col-span-12 md:col-span-2 md:order-1 rounded bg-gray-300">Menu</div>
      <div class="col-span-12 md:col-span-8 md:order-2 order-3 rounded bg-gray-300">Content</div>
      <div class="col-span-12 md:col-span-2 md:order-3 order-2 rounded bg-gray-300">Menu 2</div>
    </div>

    <div class="grid grid-cols-12 gap-2 mt-2">
      <div class="col-span-12 md:col-span-2 rounded bg-gray-300">Menu</div>
      <div class="col-span-12 md:col-span-10 rounded bg-gray-300">Content</div>
    </div>

    <div class="grid grid-cols-12 gap-2 mt-2">
      <div class="col-span-12 md:col-span-10 md:order-1 order-2 rounded bg-gray-300">Content</div>
      <div class="col-span-12 md:col-span-2 md:order-2 order-1 rounded bg-gray-300">Menu</div>
    </div>

  </main>

  <footer class="h-10 rounded bg-gray-300 mt-2">
    Footer
  </footer>
</template>