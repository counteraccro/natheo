<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Composant pour l'affichage du footer en colonne
 */
export default {
  name: 'FooterColonne',
  props: {
    elements: Object
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
  <ul class="mt-6 space-y-4">
    <li>
      <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Web Development</a>
    </li>
    <li>
      <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Pricing</a>
    </li>
    <li>
      <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Support</a>
    </li>
    <li>
      <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Client Portal</a>
    </li>
    <li>
      <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Resources</a>
    </li>
  </ul>
</template>