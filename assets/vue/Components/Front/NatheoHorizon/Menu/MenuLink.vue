<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Lien d'un élément de menu (simple titre sans lien), carré de couleur sur la page affichée
 */
import { computed } from 'vue';
import { useMenus } from '@/vue/Composables/Front/useMenus';
import type { MenuElement } from '@/ts/Front/Api/Menu.type';

const props = defineProps<{ element: MenuElement; dot?: string }>();
const { hrefOf, isActive } = useMenus();
const href = computed(() => hrefOf(props.element));
// Seule la page de l'élément compte, pas celles de ses enfants
const current = computed(() => isActive({ ...props.element, children: [] }));
</script>

<template>
  <a
    v-if="href"
    :href="href"
    :target="element.target || undefined"
    :rel="element.target === '_blank' ? 'noopener' : undefined"
    :aria-current="current ? 'page' : undefined"
  >
    <span v-if="current" class="size-2 shrink-0 rounded-[2px]" :class="dot ?? 'bg-primary'" aria-hidden="true" />
    {{ element.label }}
  </a>
  <span v-else>{{ element.label }}</span>
</template>
