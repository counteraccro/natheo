<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Requête SQL d'une grille, colorée, avec Enregistrer (vers le gestionnaire SQL), Copier et Masquer
 */
import { computed, onBeforeUnmount, ref } from 'vue';
import UiIcon from '@/vue/Components/Global/Ui/UiIcon.vue';
import { useUiLabels } from '@/vue/Composables/Admin/Ui/useUiLabels';
import { tokenizeSql } from '@/vue/Composables/Admin/Ui/sqlTokens';
import { copyToClipboard } from '@/vue/Composables/Global/copyToClipboard';
import type { SqlToken } from '@/ts/Admin/Ui/Data/SqlToken.type';

/** saved : la requête affichée est déjà enregistrée, le bouton est désactivé */
const props = defineProps<{ sql: string; saved?: boolean }>();
const emit = defineEmits<{ save: []; hide: [] }>();
const labels = useUiLabels();
const tokens = computed(() => tokenizeSql(props.sql));
const copied = ref(false);
let timer: ReturnType<typeof setTimeout> | undefined;

const copy = async (): Promise<void> => {
  try {
    await copyToClipboard(props.sql);
  } catch {
    return;
  }
  copied.value = true;
  clearTimeout(timer);
  timer = setTimeout(() => (copied.value = false), 2000);
};
onBeforeUnmount(() => clearTimeout(timer));

const COLORS: Record<SqlToken['kind'], string> = {
  keyword: 'font-bold text-code-keyword',
  string: 'text-code-string',
  number: 'text-code-number',
  identifier: 'text-code-identifier',
  comment: 'italic text-code-comment',
  plain: '',
};

const BUTTON_CLASSES =
  'inline-flex h-[30px] cursor-pointer items-center gap-1.5 rounded-sm border-0 bg-transparent px-2.5 text-[12.5px] font-semibold text-on-code-muted hover:bg-white/8 hover:text-on-code focus-visible:outline-2 focus-visible:outline-offset-0 focus-visible:outline-mod-third-b disabled:cursor-default disabled:bg-transparent disabled:text-on-code-muted';
</script>

<template>
  <section
    class="mx-4 mt-3.5 mb-3.5 overflow-hidden rounded-md bg-code text-on-code shadow-[inset_0_0_0_1px_rgb(255_255_255/.06)]"
    :aria-label="labels.dataTable.sqlTitle"
  >
    <header class="flex items-center gap-2.5 border-b border-white/8 py-2 pr-2 pl-3.5">
      <UiIcon name="code" class="size-4 text-on-code-muted" />
      <b class="text-sm font-[650]">{{ labels.dataTable.sqlTitle }}</b>
      <div class="ml-auto flex gap-0.5">
        <button type="button" :class="BUTTON_CLASSES" :disabled="saved" @click="emit('save')">
          <UiIcon :name="saved ? 'check' : 'database'" class="size-4" />{{
            saved ? labels.dataTable.sqlSaved : labels.dataTable.sqlSave
          }}
        </button>
        <button type="button" :class="BUTTON_CLASSES" @click="copy">
          <UiIcon :name="copied ? 'check' : 'copy'" class="size-4" />{{
            copied ? labels.dataTable.sqlCopied : labels.dataTable.sqlCopy
          }}
        </button>
        <button type="button" :class="BUTTON_CLASSES" @click="emit('hide')">
          <UiIcon name="eye-slash" class="size-4" />{{ labels.dataTable.sqlHide }}
        </button>
      </div>
    </header>
    <p class="sr-only" role="status">{{ copied ? labels.dataTable.sqlCopied : '' }}</p>
    <pre class="m-0 overflow-x-auto px-4 py-3.5 font-mono text-[12.5px] leading-[1.65]"><code><span
      v-for="(token, index) in tokens"
      :key="index"
      :class="COLORS[token.kind]"
    >{{ token.text }}</span></code></pre>
  </section>
</template>
