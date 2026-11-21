<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Couleur : sélecteur natif et code hexadécimal, seule une valeur #rrggbb est transmise ; couleurs proposées au
 * hasard en option
 */
import { computed, ref, watch } from 'vue';
import { useFieldControl } from '@/vue/Composables/Admin/Ui/useField';
import { useUiLabels } from '@/vue/Composables/Admin/Ui/useUiLabels';
import { CONTROL_CLASSES } from '@/vue/Composables/Admin/Ui/controlClasses';
import UiButton from '@admin/Ui/Form/UiButton.vue';

const HEX = /^#[0-9a-fA-F]{6}$/;

const props = withDefaults(
  defineProps<{ /** Nombre de couleurs proposées, aucune par défaut */ suggestions?: number }>(),
  {
    suggestions: 0,
  }
);
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

const randomColor = (): string =>
  '#' +
  Math.floor(Math.random() * 0x1000000)
    .toString(16)
    .padStart(6, '0');
const proposed = ref<string[]>([]);
const propose = (): void => {
  proposed.value = Array.from({ length: props.suggestions }, randomColor);
};
propose();

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
  <div
    v-if="suggestions > 0"
    class="mt-2 flex flex-wrap items-center gap-1.5"
    role="group"
    :aria-label="labels.colorInput.suggestions"
  >
    <button
      v-for="color in proposed"
      :key="color"
      type="button"
      :title="color"
      :aria-label="labels.colorInput.use(color)"
      :aria-pressed="color === model"
      class="ui-ring size-7 cursor-pointer rounded-md border-0 shadow-[inset_0_0_0_1px_var(--field)] aria-pressed:outline-2 aria-pressed:outline-offset-2 aria-pressed:outline-primary"
      :style="{ background: color }"
      @click="model = color"
    />
    <UiButton variant="ghost" size="sm" icon="refresh" class="ml-1" @click="propose">
      {{ labels.colorInput.more }}
    </UiButton>
  </div>
</template>
