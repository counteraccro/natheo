<script lang="ts">
/**
 * Retourne le schema d'une table
 * @author Gourdon Aymeric
 * @version 1.1
 */
import { defineComponent, type PropType } from 'vue';
import type { SchemaTableData } from '@/ts/DatabaseManager/SchemaTable.type';

export default defineComponent({
  name: 'SchemaTable',
  props: {
    data: {
      type: Object as PropType<SchemaTableData>,
      required: true,
    },
  },
});
</script>

<template>
  <div class="overflow-x-auto">
    <table class="w-full" aria-describedby="table">
      <thead class="bg-[var(--bg-main)]">
        <tr>
          <th
            v-for="(header, key) in data.header"
            :key="key"
            class="px-6 py-3 text-xs font-medium uppercase tracking-wider text-[var(--text-secondary)] text-center"
          >
            {{ header }}
          </th>
        </tr>
      </thead>
      <tbody class="divide-y divide-[var(--border-color)]">
        <tr v-for="(row, index) in data.result" :key="index" class="hover:bg-[var(--bg-hover)]">
          <td
            v-for="key in Object.keys(data.header)"
            :key="key"
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
