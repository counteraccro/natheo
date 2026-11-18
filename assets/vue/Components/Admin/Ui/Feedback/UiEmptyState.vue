<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * État vide : illustration en blocs, titre, texte et action principale facultative
 */
defineProps<{ title: string; text?: string }>();
</script>

<template>
  <div class="flex flex-col items-center gap-1.5 px-5 py-10 text-center">
    <div class="relative mb-2.5 h-16 w-[88px]" aria-hidden="true">
      <span class="absolute inset-x-0 top-0 h-[18px] rounded-[5px] bg-surface shadow-[inset_0_0_0_1px_var(--line)]" />
      <span class="absolute top-[23px] left-0 h-[18px] w-[41px] rounded-[5px] bg-surface shadow-[inset_0_0_0_1px_var(--line)]" />
      <span class="absolute top-[23px] right-0 h-[18px] w-[41px] rounded-[5px] bg-primary-soft shadow-[inset_0_0_0_1px_color-mix(in_srgb,var(--primary)_25%,transparent)]" />
      <span class="absolute bottom-0 left-0 h-[18px] w-[26px] rounded-[5px] bg-surface shadow-[inset_0_0_0_1px_var(--line)]" />
      <span class="absolute bottom-0 left-[31px] h-[18px] w-[26px] rounded-[5px] bg-surface shadow-[inset_0_0_0_1px_var(--line)]" />
      <span class="absolute right-0 bottom-0 h-[18px] w-[26px] rounded-[5px] bg-surface shadow-[inset_0_0_0_1px_var(--line)]" />
    </div>
    <h3 class="m-0 text-base font-[650]">{{ title }}</h3>
    <p v-if="text" class="m-0 mb-2.5 max-w-[42ch] text-fg-muted">{{ text }}</p>
    <slot />
  </div>
</template>
