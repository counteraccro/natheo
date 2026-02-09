<script>
/**
 * Retourne le schema de la base de données
 * @author Gourdon Aymeric
 * @version 1.0
 */

export default {
  name: 'schemaTable',
  components: {},
  props: {
    data: Object,
  },
  emits: [],
  data() {
    return {};
  },
  mounted() {},
  computed: {},
  methods: {},
};
</script>

<template>
  <div class="overflow-x-auto">
    <table class="w-full" aria-describedby="table">
      <thead class="bg-[var(--bg-main)]">
        <tr>
          <th
            v-for="(header, key) in this.data.header"
            class="px-6 py-3 text-xs font-medium uppercase tracking-wider text-[var(--text-secondary)] text-center"
          >
            {{ header }}
          </th>
        </tr>
      </thead>
      <tbody class="divide-y divide-[var(--border-color)]">
        <tr v-for="row in this.data.result" class="hover:bg-[var(--bg-hover)]">
          <td
            v-for="(header, key) in this.data.header"
            class="px-3 py-1 text-sm text-[var(--text-secondary)] text-center h-[3.4rem]"
          >
            {{ row[key] }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped></style>
