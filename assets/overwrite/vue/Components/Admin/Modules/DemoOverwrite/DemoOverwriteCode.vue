<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Bloc de code du tutoriel, précédé du chemin du fichier
 */
defineProps<{ file: string; code: string }>();
</script>

<template>
  <figure class="m-0 overflow-hidden rounded-md bg-code">
    <figcaption class="border-b border-code-raised px-4 py-2 font-mono text-xs text-on-code-muted">
      {{ file }}
    </figcaption>
    <pre class="m-0 overflow-x-auto px-4 py-3 font-mono text-[13px] leading-relaxed text-on-code"><code>{{
      code.trimEnd()
    }}</code></pre>
  </figure>
</template>
