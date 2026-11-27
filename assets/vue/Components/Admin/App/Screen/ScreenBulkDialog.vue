<script setup lang="ts" generic="T extends ScreenItem">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Dialogue du module ouvert par une action groupée d'un écran de liste, commun aux thèmes : reçoit { count, kept },
 * sa réponse (submit ou cancel) reprend l'action
 */
import type { CollectionScreenState } from '@/ts/Admin/Screen/CollectionScreen.type';
import type { ScreenItem } from '@/ts/Admin/Screen/Screen.type';

defineProps<{ screen: CollectionScreenState<T> }>();
</script>

<template>
  <component
    :is="screen.bulkDialog.value.component"
    v-if="screen.bulkDialog.value"
    :count="screen.bulkDialog.value.count"
    :kept="screen.bulkDialog.value.kept"
    @submit="(payload: Record<string, unknown>) => screen.answerBulkDialog(payload)"
    @cancel="screen.answerBulkDialog(null)"
  />
</template>
