<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Élément d'un UiDropdown : action, lien, case (menuitemcheckbox) ou choix unique (menuitemradio)
 */
import { computed, inject } from 'vue';
import UiIcon from '@/vue/Components/Global/Ui/UiIcon.vue';
import { DROPDOWN, MENU_ITEM_CLASSES } from '@/vue/Composables/Admin/Ui/dropdownContext';
import type { IconName } from '@/ts/Global/Ui/UiIcon.type';
import type { DropdownItemType } from '@/ts/Admin/Ui/Overlay/UiDropdown.type';

const props = withDefaults(
  defineProps<{
    icon?: IconName;
    href?: string;
    danger?: boolean;
    disabled?: boolean;
    type?: DropdownItemType;
    checked?: boolean;
    shortcut?: string;
  }>(),
  { type: 'item' }
);
const emit = defineEmits<{ select: [] }>();
const menu = inject(DROPDOWN, null);

const role = computed(() => ({ item: 'menuitem', checkbox: 'menuitemcheckbox', radio: 'menuitemradio' })[props.type]);

const select = (event: Event): void => {
  if (props.disabled) {
    event.preventDefault();
    return;
  }
  emit('select');
  // Une case reste ouverte pour en cocher plusieurs
  if (props.type !== 'checkbox') menu?.close(!props.href);
};
</script>

<template>
  <component
    :is="href && !disabled ? 'a' : 'button'"
    :href="href && !disabled ? href : undefined"
    :type="href ? undefined : 'button'"
    :role="role"
    tabindex="-1"
    :aria-checked="type === 'item' ? undefined : checked"
    :aria-disabled="disabled || undefined"
    :class="[MENU_ITEM_CLASSES, danger && 'text-danger hover:bg-danger-soft focus:bg-danger-soft']"
    @click="select"
    @keydown.space.prevent="select"
  >
    <UiIcon
      v-if="type !== 'item'"
      name="check"
      class="size-4 text-primary"
      :class="checked ? 'visible' : 'invisible'"
    />
    <UiIcon v-else-if="icon" :name="icon" class="size-4" :class="danger ? 'text-danger' : 'text-fg-subtle'" />
    <span class="flex-1"><slot /></span>
    <kbd v-if="shortcut" class="ml-auto font-sans text-xs text-fg-subtle">{{ shortcut }}</kbd>
  </component>
</template>
