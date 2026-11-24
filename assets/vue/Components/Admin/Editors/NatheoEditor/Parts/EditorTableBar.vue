<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Sous-barre « Tableau » de Natheo-Editor, montrée quand le curseur est dans un tableau ; la dernière commande
 * (suppression du tableau) est placée à droite
 */
import { computed } from 'vue';
import { UiIcon } from '@ui';
import { useRovingToolbar } from '@/vue/Composables/Admin/Editor/NatheoEditor/useRovingToolbar';
import type { NatheoEditorView } from '@/ts/Admin/Editor/NatheoEditor/NatheoEditor.type';

const props = defineProps<{ view: NatheoEditorView }>();
const commands = computed(() => (props.view.source.value ? [] : props.view.group('table')));
const { onFocusin, onKeydown } = useRovingToolbar();
</script>

<template>
  <div
    v-if="commands.length"
    ref="toolbar"
    role="toolbar"
    :aria-label="view.t('admin_ui.natheo_editor.table_toolbar')"
    :aria-controls="view.props.id"
    class="flex flex-wrap items-center gap-0.5 border-b border-line bg-surface px-1.5 py-1"
    @focusin="onFocusin"
    @keydown="onKeydown"
  >
    <span class="mr-2 ml-1 inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-fg-subtle">
      <UiIcon name="table" class="size-4" />
      {{ view.t('admin_ui.natheo_editor.table_toolbar') }}
    </span>
    <button
      v-for="(command, index) in commands"
      :key="command.id"
      type="button"
      :aria-disabled="view.isEnabled(command) ? undefined : true"
      :class="index === commands.length - 1 && 'ml-auto'"
      class="inline-flex h-8 cursor-pointer items-center gap-1.5 rounded-[7px] border-0 bg-transparent px-2 text-[12.5px] font-semibold text-fg-muted hover:bg-bg hover:text-fg aria-disabled:cursor-default aria-disabled:opacity-40"
      @mousedown.prevent
      @click="view.isEnabled(command) && view.run(command)"
    >
      <UiIcon :name="command.icon" class="size-4" />
      {{ view.shortLabel(command) }}
    </button>
  </div>
</template>
