<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Écran décrit par des données : calcule son état et le donne au rendu du thème actif
 */
import { useAdminTheme, toComponent } from '@/vue/Composables/Admin/Screen/adminTheme';
import { useCollectionScreen } from '@/vue/Composables/Admin/Screen/useCollectionScreen';
import { useFormScreen } from '@/vue/Composables/Admin/Screen/useFormScreen';
import type { CollectionScreenDescriptor } from '@/ts/Admin/Screen/CollectionScreen.type';
import type { FormScreenDescriptor } from '@/ts/Admin/Screen/FormScreen.type';

const props = defineProps<{
  kind: 'collection' | 'form';
  descriptor: CollectionScreenDescriptor | FormScreenDescriptor;
  /** Formulaire : identifiant de l'élément, null pour une création */
  id?: string | null;
}>();

const theme = useAdminTheme();
const screen =
  props.kind === 'collection'
    ? useCollectionScreen(props.descriptor as CollectionScreenDescriptor)
    : useFormScreen(props.descriptor as FormScreenDescriptor, props.id ?? null);
const component = toComponent(props.kind === 'collection' ? theme.screens.collection : theme.screens.form);
</script>

<template>
  <component :is="component" :screen="screen" />
</template>
