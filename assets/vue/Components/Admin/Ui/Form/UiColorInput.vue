<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Couleur : sélecteur natif et code hexadécimal, seule une valeur #rrggbb est transmise
 */
import { computed, ref, watch } from 'vue';
import { useFieldControl } from '@/vue/Composables/Admin/Ui/useField';
import { useUiLabels } from '@/vue/Composables/Admin/Ui/useUiLabels';
import { CONTROL_CLASSES } from '@/vue/Composables/Admin/Ui/controlClasses';

const HEX = /^#[0-9a-fA-F]{6}$/;

const model = defineModel<string>({ default: '#000000' });
const labels = useUiLabels();
const text = ref(model.value);
const invalid = computed(() => !HEX.test(text.value));
const control = useFieldControl(invalid);

watch(model, (value) => {
  if (value.toLowerCase() !== text.value.toLowerCase()) {
    text.value = value;
  }
});

const onText = (value: string): void => {
  text.value = value.trim();
  if (HEX.test(text.value)) {
    model.value = text.value.toLowerCase();
  }
};
</script>

<template>
  <div class="flex items-center gap-2">
    <span class="relative size-9 shrink-0 overflow-hidden rounded-md shadow-[inset_0_0_0_1px_var(--field)]">
      <input
        type="color"
        :value="HEX.test(model) ? model : '#000000'"
        :aria-label="labels.colorInput.picker"
        class="absolute -inset-2 size-[52px] cursor-pointer border-0 p-0"
        @input="model = ($event.target as HTMLInputElement).value"
      />
    </span>
    <input
      v-bind="control"
      :value="text"
      type="text"
      maxlength="7"
      spellcheck="false"
      autocomplete="off"
      :title="labels.colorInput.hex"
      :class="[CONTROL_CLASSES, 'h-9 w-32 px-3 font-mono text-sm lowercase']"
      @input="onText(($event.target as HTMLInputElement).value)"
    />
  </div>
</template>
