<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Lien d'un élément de menu (simple titre sans lien), icône pour un lien ouvert dans un nouvel onglet
 */
import { computed } from 'vue';
import { useMenus } from '@/vue/Composables/Front/useMenus';
import UiIcon from '@/vue/Components/Global/Ui/UiIcon.vue';
import type { MenuElement } from '@/ts/Front/Api/Menu.type';

const props = defineProps<{ element: MenuElement }>();
const { hrefOf, isActive } = useMenus();
const href = computed(() => hrefOf(props.element));
// Seule la page de l'élément compte, pas celles de ses enfants
const current = computed(() => isActive({ ...props.element, children: [] }));
const newTab = computed(() => props.element.target === '_blank');
</script>

<template>
  <a
    v-if="href"
    :href="href"
    :target="element.target || undefined"
    :rel="newTab ? 'noopener' : undefined"
    :aria-current="current ? 'page' : undefined"
  >
    {{ element.label }}
    <UiIcon v-if="newTab" name="external" class="size-3.5 opacity-70" />
  </a>
  <span v-else>{{ element.label }}</span>
</template>
