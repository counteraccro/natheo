<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Structure de la page de front
 */
export default {
  name: 'NavMegaMenu',
  props: {
    data: Object,
    utilsFront: Object
  },
  emits: [],
  data() {
    return {
    }
  },
  mounted() {

  },

  methods: {}
}
</script>

<template>
  <div class="relative group inline-block">
    <!-- Bouton principal -->
    <a href="#" class="text-lg hover:text-blue-500 cursor-pointer py-2 px-4 inline-flex items-center gap-1">
      Mega Menu <span>▼</span>
    </a>

    <!-- Mega menu panel -->
    <div class="absolute left-0 hidden group-hover:block group-focus-within:block bg-gray-100 shadow-xl z-20 w-[700px]
              opacity-0 translate-y-3 transition-all duration-300 ease-out group-hover:opacity-100 group-hover:translate-y-0 group-focus-within:opacity-100 group-focus-within:translate-y-0">
      <div class="grid grid-cols-3 gap-6 p-6">
        <!-- Colonne 1 -->
        <div>
          <h3 class="text-sm font-semibold text-gray-700 mb-2">Produits</h3>
          <ul>
            <li><a href="/prod1" class="block px-2 py-1 hover:bg-gray-200 rounded">Produit 1</a></li>
            <li><a href="/prod2" class="block px-2 py-1 hover:bg-gray-200 rounded">Produit 2</a></li>
            <li><a href="/prod3" class="block px-2 py-1 hover:bg-gray-200 rounded">Produit 3</a></li>
          </ul>
        </div>

        <!-- Colonne 2 -->
        <div>
          <h3 class="text-sm font-semibold text-gray-700 mb-2">Solutions</h3>
          <ul>
            <li><a href="/sol1" class="block px-2 py-1 hover:bg-gray-200 rounded">Solution 1</a></li>
            <li><a href="/sol2" class="block px-2 py-1 hover:bg-gray-200 rounded">Solution 2</a></li>
            <li><a href="/sol3" class="block px-2 py-1 hover:bg-gray-200 rounded">Solution 3</a></li>
          </ul>
        </div>

        <!-- Colonne 3 -->
        <div>
          <h3 class="text-sm font-semibold text-gray-700 mb-2">Ressources</h3>
          <ul>
            <li><a href="/doc" class="block px-2 py-1 hover:bg-gray-200 rounded">Documentation</a></li>
            <li><a href="/blog" class="block px-2 py-1 hover:bg-gray-200 rounded">Blog</a></li>
            <li><a href="/support" class="block px-2 py-1 hover:bg-gray-200 rounded">Support</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>

</template>