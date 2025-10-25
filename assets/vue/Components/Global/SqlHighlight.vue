<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * SQL formateur
 */
export default {
  name: 'SqlHighLight',
  props: {
    sql: { type: String, required: true },
    label: { type: String, default: 'SQL' },
    format: { type: Boolean, default: true },
  },
  emits: ['hide-sql', 'save-sql', 'copy-sql'],
  data() {
    return { copied: false };
  },
  computed: {
    formattedSql() {
      if (!this.format) return this.sql;
      let sql = this.sql.trim();
      sql = sql.replace(
        /\s+(FROM|WHERE|JOIN|LEFT JOIN|RIGHT JOIN|INNER JOIN|OUTER JOIN|GROUP BY|HAVING|ORDER BY|LIMIT|UNION|INTERSECT|EXCEPT)\s+/gi,
        '\n$1 '
      );
      sql = sql.replace(/SELECT\s+/gi, 'SELECT\n  ');
      sql = sql.replace(/,\s*(?=\w)/g, ',\n  ');
      sql = sql.replace(/\s+/g, ' ');
      sql = sql.replace(/\s*\n\s*/g, '\n');
      return sql;
    },

    highlightedSql() {
      let html = this.formattedSql.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
      const keywords = [
        'SELECT',
        'FROM',
        'WHERE',
        'JOIN',
        'LEFT',
        'RIGHT',
        'INNER',
        'OUTER',
        'FULL',
        'CROSS',
        'ON',
        'USING',
        'GROUP BY',
        'ORDER BY',
        'HAVING',
        'LIMIT',
        'OFFSET',
        'INSERT',
        'INTO',
        'VALUES',
        'UPDATE',
        'SET',
        'DELETE',
        'CREATE',
        'ALTER',
        'DROP',
        'TABLE',
        'VIEW',
        'INDEX',
        'DATABASE',
        'SCHEMA',
        'AND',
        'OR',
        'NOT',
        'IN',
        'EXISTS',
        'BETWEEN',
        'LIKE',
        'IS',
        'NULL',
        'AS',
        'DISTINCT',
        'ALL',
        'UNION',
        'INTERSECT',
        'EXCEPT',
        'CASE',
        'WHEN',
        'THEN',
        'ELSE',
        'END',
        'WITH',
        'RECURSIVE',
        'ASC',
        'DESC',
      ];
      keywords.forEach((keyword) => {
        const regex = new RegExp(`\\b(${keyword})\\b`, 'gi');
        html = html.replace(regex, '<span class="text-[var(--primary)]">$1</span>');
      });

      const functions = [
        'COUNT',
        'SUM',
        'AVG',
        'MAX',
        'MIN',
        'ROUND',
        'CEIL',
        'FLOOR',
        'ABS',
        'CONCAT',
        'SUBSTRING',
        'LENGTH',
        'UPPER',
        'LOWER',
        'TRIM',
        'REPLACE',
        'COALESCE',
        'NULLIF',
        'CAST',
        'CONVERT',
        'NOW',
        'CURRENT_DATE',
        'CURRENT_TIMESTAMP',
        'DATE',
        'TIME',
        'YEAR',
        'MONTH',
        'DAY',
        'EXTRACT',
        'DATE_ADD',
        'DATE_SUB',
        'DATEDIFF',
      ];
      functions.forEach((func) => {
        const regex = new RegExp(`\\b(${func})\\s*\\(`, 'gi');
        html = html.replace(regex, '<span class="text-[var(--primary)]">$1</span>(');
      });
      html = html.replace(/('(?:[^'\\]|\\.)*')/g, '<span class="text-[var(--alert-info)]">$1</span>');
      html = html.replace(/\b(\d+\.?\d*)\b/g, '<span class="text-[var(--alert-success)]">$1</span>');

      return html;
    },
  },
  methods: {
    async copyToClipboard() {
      this.$emit('copy-sql');
    },
  },
};
</script>

<template>
  <div class="bg-[var(--bg-card)] rounded-xl shadow-sm border border-[var(--border-color)] mt-3">
    <div class="flex items-center justify-between px-4 py-3 bg-[var(--bg-main)] rounded-xl">
      <span class="text-sm font-mono text-slate-400">$ {{ label }}</span>
      <div>
        <button @click="this.$emit('save-sql')" class="btn-icon btn btn-ghost-primary">
          <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M11 16h2m6.707-9.293-2.414-2.414A1 1 0 0 0 16.586 4H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V7.414a1 1 0 0 0-.293-.707ZM16 20v-6a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v6h8ZM9 4h6v3a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1V4Z"
            ></path>
          </svg>
        </button>
        <button @click="copyToClipboard" class="btn-icon btn btn-ghost-primary">
          <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
            ></path>
          </svg>
        </button>
        <button @click="this.$emit('hide-sql')" class="btn-icon btn btn-ghost-primary">
          <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="m15 9-6 6m0-6 6 6m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
            ></path>
          </svg>
        </button>
      </div>
    </div>
    <div class="p-5">
      <pre><code class="whitespace-pre-wrap break-words text-[var(--text-primary)]" v-html="highlightedSql"></code></pre>
    </div>
  </div>
</template>

<style></style>
