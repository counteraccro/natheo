<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Statut de modération d'un commentaire, colonne de la liste : ton et icône du statut
 */
import { computed } from 'vue';
import { UiBadge, UiIcon } from '@ui';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { commentStatus } from '@/vue/Composables/Admin/Comment/commentStatus';

const props = defineProps<{ value: unknown }>();

const { t } = useAdminContext();
const status = computed(() => commentStatus(String(props.value ?? '')));
</script>

<template>
  <UiBadge :tone="status.tone" :dot="false">
    <UiIcon :name="status.icon" class="size-3.5" />
    {{ t(status.label) }}
  </UiBadge>
</template>
