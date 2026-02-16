<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 3.0
 * Éditeur Markdown — Vue 3 Composition API (defineComponent + setup)
 */

import { defineComponent, ref, computed, watch, type PropType } from 'vue';
import { useEditor } from '@/ts/MarkdownEditor/markdownEditorCore';
import type {
  EditorModule,
  MarkdownToolbar,
  MarkdownToolbarButtonName,
} from '@/ts/MarkdownEditor/MarkdownEditor.types';
import axios from 'axios';
import InternalLink from '@/vue/Components/Global/MarkdownEditor/InternalLink.vue';

// ─── Types ────────────────────────────────────────────────────────────────────

interface KeyWord {
  label: string;
  keyword: string;
}

interface ButtonDef {
  title: string;
  icon: string;
}

// ─── Icônes SVG ───────────────────────────────────────────────────────────────

const sv = (attrs: string, body: string): string => `<svg class="tb-icon" viewBox="0 0 24 24" ${attrs}>${body}</svg>`;

const f = (body: string): string => sv('fill="currentColor"', body);

const s = (body: string): string =>
  sv('fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"', body);

const BUTTON_DEFS: Record<string, ButtonDef> = {
  bold: {
    title: 'Gras (Ctrl+B)',
    icon: f(
      '<path d="M15.6 10.79c.97-.67 1.65-1.77 1.65-2.79 0-2.26-1.75-4-4-4H7v14h7.04c2.09 0 3.71-1.7 3.71-3.79 0-1.52-.86-2.82-2.15-3.42zM10 6.5h3c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5h-3v-3zm3.5 9H10v-3h3.5c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5z"/>'
    ),
  },
  italic: {
    title: 'Italique (Ctrl+I)',
    icon: f('<path d="M10 4v3h2.21l-3.42 8H6v3h8v-3h-2.21l3.42-8H18V4z"/>'),
  },
  strikethrough: {
    title: 'Barré',
    icon: s('<path d="M16 4H9a3 3 0 000 6h6a3 3 0 010 6H6"/><line x1="4" y1="12" x2="20" y2="12"/>'),
  },
  blockquote: {
    title: 'Citation',
    icon: f('<path d="M6 17h3l2-4V7H5v6h3zm8 0h3l2-4V7h-6v6h3z"/>'),
  },
  bulletList: {
    title: 'Liste à puces',
    icon: s(
      '<line x1="9" y1="6" x2="20" y2="6"/><line x1="9" y1="12" x2="20" y2="12"/><line x1="9" y1="18" x2="20" y2="18"/><circle cx="4" cy="6" r="1.5" fill="currentColor" stroke="none"/><circle cx="4" cy="12" r="1.5" fill="currentColor" stroke="none"/><circle cx="4" cy="18" r="1.5" fill="currentColor" stroke="none"/>'
    ),
  },
  orderedList: {
    title: 'Liste numérotée',
    icon: f(
      '<path d="M2 17h2v.5H3v1h1v.5H2v1h3v-4H2v1zm1-9h1V4H2v1h1v3zm-1 3h1.8L2 13.1v.9h3v-1H3.2L5 10.9V10H2v1zm5-6v2h14V5H7zm0 14h14v-2H7v2zm0-6h14v-2H7v2z"/>'
    ),
  },
  table: {
    title: 'Tableau',
    icon: s(
      '<rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="12" y1="3" x2="12" y2="21"/>'
    ),
  },
  link: {
    title: 'Lien (Ctrl+K)',
    icon: s(
      '<path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/>'
    ),
  },
  image: {
    title: 'Image',
    icon: s(
      '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>'
    ),
  },
  code: {
    title: 'Code',
    icon: s('<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>'),
  },
};

// ─── Composant ────────────────────────────────────────────────────────────────

export default defineComponent({
  name: 'MarkdownEditor',
  components: { InternalLink },

  props: {
    meId: {
      type: String,
      default: 'mdInput',
    },
    meValue: {
      type: String,
      default: '',
    },
    meRows: {
      type: Number,
      default: 12,
    },
    meSave: {
      type: Boolean,
      default: false,
    },
    mePreview: {
      type: Boolean,
      default: true,
    },
    meTranslate: {
      type: Object as PropType<Record<string, string>>,
      default: () => ({}),
    },
    meKeyWords: {
      type: Array as PropType<KeyWord[]>,
      default: () => [],
    },
    meModules: {
      type: Array as PropType<EditorModule[]>,
      default: () => [],
    },
    meToolbar: {
      type: Array as PropType<MarkdownToolbar>,
      default: (): MarkdownToolbar => [
        ['heading', 'keywords'],
        ['bold', 'italic', 'strikethrough', 'blockquote'],
        ['bulletList', 'orderedList', 'table'],
        ['link', 'image', 'code'],
        ['save'],
      ],
    },
  },

  emits: ['editor-value', 'editor-value-change'],

  data() {
    return {
      urls: {
        urlMedia: '',
        urlPreview: '',
        urlSetPreview: '',
        urlInternalLink: '',
        urlLoadData: '/admin/fr/markdown/ajax/load-datas',
      },
    };
  },

  mounted() {
    this.loadData();
  },

  setup(props, { emit }) {
    // ── Composable éditeur ───────────────────────────────────────────────────
    const editor = useEditor(props.meValue);

    // Alias pour le template
    const textareaRef = editor.textareaRef;
    const markdown = editor.markdown;
    const html = editor.html;
    const wordCount = editor.wordCount;

    // ── Toolbar résolue ──────────────────────────────────────────────────────
    // 'save' est exclu ici — il est rendu séparément en fin de toolbar,
    // APRÈS les modules custom, pour être toujours tout à droite.

    const resolvedGroups = computed<MarkdownToolbarButtonName[][]>(() =>
      props.meToolbar
        .map((group) =>
          group.filter((name) => {
            if (name === 'save') return false;
            if (name === 'keywords' && !props.meKeyWords?.length) return false;
            return true;
          })
        )
        .filter((group) => group.length > 0)
    );

    const hasSave = computed<boolean>(() => props.meSave && props.meToolbar.some((group) => group.includes('save')));

    // ── Refs dropdowns ───────────────────────────────────────────────────────

    const ddHeadingRef = ref<HTMLDetailsElement | null>(null);
    const ddKeywordsRef = ref<HTMLDetailsElement | null>(null);

    function closeDropdown(el: HTMLDetailsElement | null): void {
      if (el) el.removeAttribute('open');
    }

    // ── Actions toolbar ──────────────────────────────────────────────────────

    function onButtonClick(name: string): void {
      switch (name as MarkdownToolbarButtonName) {
        case 'bold':
          editor.wrapSelection('**', '**', 'texte en gras');
          break;
        case 'italic':
          editor.wrapSelection('*', '*', 'texte en italique');
          break;
        case 'strikethrough':
          editor.wrapSelection('~~', '~~', 'texte barré');
          break;
        case 'blockquote':
          editor.insertLinePrefix('> ');
          break;
        case 'bulletList':
          editor.insertLinePrefix('- ');
          break;
        case 'orderedList':
          editor.insertLinePrefix('1. ');
          break;
        case 'code':
          editor.wrapSelection('`', '`', 'code');
          break;
        case 'link':
          editor.wrapSelection('[', '](url)', editor.getSelection().text || 'Texte du lien');
          break;
        case 'image':
          editor.wrapSelection('![', '](url)', editor.getSelection().text || 'description');
          break;
        case 'table':
          editor.insertBlock(
            '| Colonne 1 | Colonne 2 | Colonne 3 |\n' +
              '|-----------|-----------|----------|\n' +
              '| Cellule   | Cellule   | Cellule  |'
          );
          break;
      }
    }

    function insertHeading(level: number): void {
      editor.insertLinePrefix('#'.repeat(level) + ' ');
      closeDropdown(ddHeadingRef.value);
    }

    function insertKeyword(keyword: string): void {
      editor.insertText(keyword);
      closeDropdown(ddKeywordsRef.value);
    }

    function onInput(e: Event): void {
      const val = (e.target as HTMLTextAreaElement).value;
      markdown.value = val;
      emit('editor-value-change', props.meId, val);
    }

    function onSave(): void {
      emit('editor-value', props.meId, markdown.value);
    }

    function onModuleClick(module: EditorModule): void {
      module.action({
        insertText: editor.insertText,
        wrapSelection: editor.wrapSelection,
        insertLinePrefix: editor.insertLinePrefix,
        insertBlock: editor.insertBlock,
        getSelection: editor.getSelection,
        focus: editor.focus,
        getMarkdown: editor.getMarkdown,
        setMarkdown: editor.setMarkdown,
      });
    }

    // ── Raccourcis clavier ───────────────────────────────────────────────────

    function handleKeydown(e: KeyboardEvent): void {
      if (e.key === 'Tab') {
        e.preventDefault();
        editor.insertText('  ');
        return;
      }
      if (e.ctrlKey || e.metaKey) {
        switch (e.key.toLowerCase()) {
          case 'b':
            e.preventDefault();
            onButtonClick('bold');
            break;
          case 'i':
            e.preventDefault();
            onButtonClick('italic');
            break;
          case 'k':
            e.preventDefault();
            onButtonClick('link');
            break;
        }
      }
    }

    function onBlur(): void {
      emit('editor-value', markdown.value);
    }

    // ── Utilitaires ──────────────────────────────────────────────────────────

    const textareaStyle = computed(() => ({
      minHeight: `${props.meRows * 1.75}rem`,
    }));

    function t(key: string, fallback: string): string {
      return props.meTranslate?.[key] ?? fallback;
    }

    // ── Exposition au template ────────────────────────────────────────────────

    return {
      // Refs
      textareaRef,
      ddHeadingRef,
      ddKeywordsRef,
      // État réactif
      markdown,
      html,
      wordCount,
      // Constantes
      BUTTON_DEFS,
      // Computed
      resolvedGroups,
      hasSave,
      textareaStyle,
      // Méthodes
      onButtonClick,
      insertHeading,
      insertKeyword,
      onInput,
      onSave,
      onBlur,
      handleKeydown,
      t,
      onModuleClick,
    };
  },

  methods: {
    /** Charge les données nécessaires au fonctionnement de l'éditeur **/
    loadData() {
      axios
        .get(this.urls.urlLoadData)
        .then((response) => {
          this.urls.urlMedia = response.data.media;
          this.urls.urlPreview = response.data.preview;
          this.urls.urlSetPreview = response.data.initPreview;
          this.urls.urlInternalLink = response.data.internalLinks;
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;
        });
    },
  },
});
</script>

<template>
  <div class="md-editor-wrap">
    <!-- ── TOOLBAR ─────────────────────────────────────── -->
    <div class="md-toolbar">
      <!-- 1. Groupes de boutons (save exclu) -->
      <template v-for="(group, gi) in resolvedGroups" :key="gi">
        <div v-if="gi > 0" class="tb-sep" />

        <template v-for="name in group" :key="name">
          <!-- Dropdown Titres -->
          <details v-if="name === 'heading'" class="tb-dropdown" ref="ddHeadingRef">
            <summary class="tb-btn" style="padding: 0 0.625rem">
              H
              <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="M6 9l6 6 6-6" />
              </svg>
            </summary>
            <div class="tb-dropdown-menu" style="min-width: 12rem">
              <a
                v-for="level in [1, 2, 3, 4, 5, 6]"
                :key="level"
                href="#"
                class="tb-dropdown-item"
                @click.prevent="insertHeading(level)"
              >
                <span class="item-badge">H{{ level }}</span>
                <span class="item-label" :class="`h-preview-${level}`">{{
                  t(`heading${level}`, `Titre ${level}`)
                }}</span>
                <span class="item-shortcut">{{ '#'.repeat(level) + ' ' }}</span>
              </a>
            </div>
          </details>

          <!-- Dropdown Mots-clés -->
          <details v-else-if="name === 'keywords'" class="tb-dropdown" ref="ddKeywordsRef">
            <summary class="tb-btn" style="padding: 0 0.75rem">
              <svg
                class="tb-icon"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path
                  d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"
                />
              </svg>
              {{ t('keywords', 'Mots clés') }}
              <svg
                class="tb-icon"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="M6 9l6 6 6-6" />
              </svg>
            </summary>
            <div class="tb-dropdown-menu" style="min-width: 13rem">
              <a
                v-for="kw in meKeyWords"
                :key="kw.keyword"
                href="#"
                class="tb-dropdown-item"
                @click.prevent="insertKeyword(kw.keyword)"
              >
                <span class="item-badge">@</span>
                <span class="item-label">{{ kw.label }}</span>
                <span class="item-shortcut">{{ kw.keyword }}</span>
              </a>
            </div>
          </details>

          <!-- Boutons simples -->
          <button
            v-else-if="BUTTON_DEFS[name]"
            class="tb-btn"
            :title="t(name, BUTTON_DEFS[name].title)"
            @click="onButtonClick(name)"
            v-html="BUTTON_DEFS[name].icon"
          />
        </template>
      </template>

      <!-- 2. Modules custom -->
      <template v-if="meModules.length > 0">
        <div class="tb-sep" />
        <button
          v-for="mod in meModules"
          :key="mod.name"
          class="tb-btn"
          :title="mod.label"
          v-html="mod.icon"
          @click="onModuleClick(mod)"
        />
      </template>

      <!-- 3. Save — toujours en dernier grâce à margin-left: auto -->
      <button v-if="hasSave" class="tb-btn tb-btn-save" :title="t('save', 'Sauvegarder')" @click="onSave">
        <svg
          class="tb-icon"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
          <polyline points="17 21 17 13 7 13 7 21" />
          <polyline points="7 3 7 8 15 8" />
        </svg>
        {{ t('save', 'Sauvegarder') }}
      </button>
    </div>

    <!-- ── TEXTAREA ──────────────────────────────────── -->
    <textarea
      :id="meId"
      ref="textareaRef"
      :value="markdown"
      class="md-textarea form-input"
      :placeholder="t('placeholder', 'Rédigez votre contenu en Markdown…')"
      :style="textareaStyle"
      @input="onInput"
      @keydown="handleKeydown"
      @blur="onBlur"
    />

    <!-- ── FOOTER ─────────────────────────────────────── -->
    <div class="md-footer-hint">
      <svg
        style="width: 0.875rem; height: 0.875rem; color: var(--text-light); flex-shrink: 0"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
      >
        <circle cx="12" cy="12" r="10" />
        <line x1="12" y1="8" x2="12" y2="12" />
        <line x1="12" y1="16" x2="12.01" y2="16" />
      </svg>
      {{ t('hint', 'La mise en forme de votre contenu se fait avec le langage de balisage') }}
      <a href="https://www.markdownguide.org" target="_blank" rel="noopener">Markdown</a>
      <span class="md-footer-counts">
        {{ wordCount }} {{ t('words', 'mots') }} · {{ markdown.length }} {{ t('chars', 'car.') }}
      </span>
    </div>
  </div>

  <!-- ── PREVIEW ────────────────────────────────────── -->
  <div v-if="mePreview" class="md-preview-wrap">
    <div class="md-preview-header">
      <svg
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
      >
        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
        <circle cx="12" cy="12" r="3" />
      </svg>
      <span>{{ t('preview', 'Prévisualisation') }}</span>
    </div>
    <div
      class="md-preview-body"
      v-html="html || `<p class='md-preview-empty'>${t('previewEmpty', 'Commencez à écrire…')}</p>`"
    />
  </div>

  <InternalLink :url="urls.urlInternalLink" />
</template>

<style scoped>
.md-footer-counts {
  margin-left: auto;
  font-size: 0.7rem;
  color: var(--text-light);
  white-space: nowrap;
}

.tb-btn-save {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0 0.75rem;
  margin-left: auto;
  background-color: var(--primary);
  color: #fff;
  border-radius: 0.375rem;
  font-size: 0.8rem;
  font-weight: 600;
  transition: background-color 0.15s ease;
}
.tb-btn-save:hover {
  background-color: var(--primary-hover);
}

.md-preview-body :deep(.md-preview-empty) {
  color: var(--text-light);
  font-style: italic;
}
</style>
