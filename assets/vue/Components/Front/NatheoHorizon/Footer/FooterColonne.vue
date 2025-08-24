<script>

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Composant pour l'affichage du footer en colonne
 */
export default {
  name: 'FooterColonne',
  props: {
    elements: Object,
    deep: Number,
    utilsFront: Object
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

    /**
     * Génère une url
     * @param element
     * @returns {*}
     */
    generateUrl(element) {
      return this.utilsFront.getUrl(element);
    }
  }
}
</script>

<template>
  <ul class="space-y-2" :class="this.deep > 0 ? 'list-inside pl-3' : 'mt-6'">
    <li v-for="element in this.elements">

      <a :href="this.generateUrl(element)" :target="element.target" class="text-slate-600 font-normal hover:bg-theme-4-750 hover:!text-theme-1-100 rounded-md hover:dark:bg-gray-600 p-1.5">
        {{element.label}}
      </a>
      <FooterColonne v-if="element.hasOwnProperty('elements')"
                     :elements="element.elements"
                     :deep="this.deep + 1"
                     :utils-front="this.utilsFront"
      />
    </li>
  </ul>
</template>