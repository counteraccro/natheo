<script lang="ts">
/**
 * Retourne le schema de la base de données
 * @author Gourdon Aymeric
 * @version 2.1
 */
import { defineComponent, type PropType } from 'vue';
import type { SchemaDatabaseData } from '@/ts/DatabaseManager/SchemaDatabase.type';

export default defineComponent({
  name: 'SchemaDatabase',
  props: {
    data: {
      type: Object as PropType<SchemaDatabaseData>,
      required: true,
    },
    tableName: {
      type: String,
      default: '',
    },
  },
  emits: {
    'load-schema-table': (table: string) => typeof table === 'string',
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
        <tr
          v-for="row in data.result"
          :key="String(row['table_name'])"
          class="hover:bg-[var(--bg-hover)]"
          :style="row['table_name'] === tableName ? 'background : var(--primary-lighter)' : ''"
        >
          <td
            v-for="key in Object.keys(data.header)"
            :key="key"
            class="px-3 py-1 text-sm text-[var(--text-secondary)] text-center"
          >
            <button
              v-if="key === 'action'"
              class="btn btn-icon m-1 btn-xs btn-ghost-primary"
              @click="$emit('load-schema-table', String(row['table_name']))"
            >
              <svg
                class="icon-sm"
                aria-hidden="true"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
              >
                <path
                  stroke="currentColor"
                  stroke-width="2"
                  d="M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z"
                ></path>
                <path stroke="currentColor" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path>
              </svg>
            </button>

            <span v-else>{{ row[key] }}</span>
          </td>
          <td></td>
        </tr>
        <tr></tr>
      </tbody>
    </table>
  </div>
</template>
