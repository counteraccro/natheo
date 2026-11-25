<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Exemple d'éditeur de texte tiers : zone de texte qui respecte le contrat des éditeurs du back-office
 */
import type { AdminEditorProps } from '@/ts/Admin/Editor/AdminEditor.type';

defineProps<AdminEditorProps>();
const emit = defineEmits<{ 'update:modelValue': [value: string] }>();
</script>

<template>
  <textarea
    :id="id"
    :value="modelValue"
    :rows="rows"
    :placeholder="placeholder"
    :lang="lang"
    :aria-labelledby="labelledBy"
    :aria-label="labelledBy ? undefined : label"
    :aria-describedby="describedBy"
    :aria-invalid="invalid || undefined"
    :required="required"
    :disabled="disabled"
    :readonly="readonly"
    class="w-full rounded-md border border-field bg-bg px-3 py-2 font-mono text-sm text-fg"
    @input="emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
  ></textarea>
</template>
