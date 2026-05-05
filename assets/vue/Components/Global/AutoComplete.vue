<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Module d'autoCompletion
 */

import { defineComponent, h, PropType } from 'vue';

export interface AutocompleteOption {
  value: string | number;
  label: string;
  hint?: string;
}

export default defineComponent({
  name: 'Autocomplete',

  props: {
    modelValue: {
      type: [String, Number] as PropType<string | number>,
      default: '',
    },
    options: {
      type: Array as PropType<AutocompleteOption[]>,
      required: true,
    },
    placeholder: {
      type: String,
      default: 'Rechercher...',
    },
    emptyLabel: {
      type: String,
      default: 'Aucun résultat',
    },
    errorValidate: {
      type: String,
      default: 'Ce champ ne peut pas être vide',
    },
    hasError: {
      type: Boolean,
      default: false,
    },
  },

  emits: ['update:modelValue', 'select', 'reset', 'change'],

  data() {
    return {
      searchQuery: '' as string,
      isOpen: false as boolean,
    };
  },

  mounted() {
    this.initLabel();
  },

  watch: {
    modelValue() {
      this.initLabel();
    },
    options() {
      this.initLabel();
    },
  },

  computed: {
    filteredOptions(): AutocompleteOption[] {
      const query = this.searchQuery.toLowerCase().trim();
      if (!query) return this.options;
      return this.options.filter((option) => option.label.toLowerCase().includes(query));
    },
  },

  methods: {
    h,
    /**
     * Pré-remplit le label si une valeur est déjà sélectionnée
     */
    initLabel(): void {
      const selected = this.options.find((o) => o.value === this.modelValue);
      this.searchQuery = selected?.label ?? '';
    },

    /**
     * Sélectionne une option
     */
    selectOption(option: AutocompleteOption): void {
      this.searchQuery = option.label;
      this.isOpen = false;
      this.$emit('update:modelValue', option.value);
      this.$emit('select', option);
      this.$emit('change');
    },

    /**
     * Réinitialise la sélection
     */
    reset(): void {
      this.searchQuery = '';
      this.isOpen = true;
      this.$emit('update:modelValue', '0');
      this.$emit('reset');
      this.$emit('change');
      // Remet le focus sur l'input pour continuer la recherche
      this.$nextTick(() => {
        const input = this.$refs.searchInput as HTMLInputElement | undefined;
        if (input) {
          input.focus();
        }
      });
    },

    onBlur(): void {
      setTimeout(() => {
        this.isOpen = false;
        this.initLabel();
      }, 150);
    },
  },
});
</script>

<template>
  <div class="relative">
    <!-- Input de recherche -->
    <div class="relative">
      <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
          />
        </svg>
      </div>
      <input
        type="text"
        class="form-input"
        :class="hasError ? 'is-invalid' : ''"
        style="padding-left: calc(var(--spacing) * 9)"
        v-model="searchQuery"
        :placeholder="placeholder"
        @focus="isOpen = true"
        @blur="onBlur"
      />
      <!-- Bouton reset -->
      <button
        v-if="searchQuery.length > 0"
        class="absolute inset-y-0 right-0 flex items-center pr-3"
        @mousedown.prevent="reset"
      >
        <svg class="w-4 h-4 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
    <span v-if="hasError" class="form-text text-error">✗ {{ errorValidate }}</span>

    <!-- Dropdown résultats -->
    <div
      v-if="isOpen && filteredOptions.length > 0"
      class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-60 overflow-y-auto"
    >
      <ul class="py-1 divide-y divide-gray-100 dark:divide-gray-700">
        <li
          v-for="option in filteredOptions"
          :key="option.value"
          class="flex items-center gap-2 px-3 py-2.5 cursor-pointer transition-colors"
          :class="
            option.value === modelValue ? 'bg-gray-100 dark:bg-gray-700' : 'hover:bg-gray-50 dark:hover:bg-gray-700'
          "
          @mousedown.prevent="selectOption(option)"
        >
          <slot name="option" :option="option">
            <span class="text-sm" style="color: var(--text-primary)">{{ option.label }}</span>
          </slot>
        </li>
      </ul>
    </div>

    <!-- Aucun résultat -->
    <div
      v-if="isOpen && searchQuery.length > 0 && filteredOptions.length === 0"
      class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg"
    >
      <p class="px-3 py-3 text-sm text-center" style="color: var(--text-light)">
        {{ emptyLabel }}
      </p>
    </div>
  </div>
</template>
