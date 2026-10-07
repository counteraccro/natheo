<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 4.0
 * Éditeur Markdown — Vue 3 Composition API (defineComponent + setup)
 */

import { computed, defineComponent, onMounted, onUnmounted, type PropType, reactive, ref, useId, watch } from 'vue';
import { loadEditorDatas, useEditor } from '@/ts/MarkdownEditor/markdownEditorCore';
import type {
  EditorApi,
  EditorModule,
  MarkdownEditorDatas,
  MarkdownEditorKeyWord,
  MarkdownEditorTranslate,
  MarkdownToolbar,
  MarkdownToolbarButton,
  MarkdownToolbarButtonName,
} from '@/ts/MarkdownEditor/MarkdownEditor.type';
import { InternalLinkModule } from '@/ts/MarkdownEditor/modules/internalLink';
import { MediaModule } from '@/ts/MarkdownEditor/modules/Mediatheque';
import InternalLink from '@/vue/Components/Global/MarkdownEditor/InternalLink.vue';
import MediathequeModale from '@/vue/Components/Global/MarkdownEditor/Mediatheque.vue';

// ─── Icônes SVG ───────────────────────────────────────────────────────────────

const sv = (attrs: string, body: string): string =>
  `<svg class="tb-icon" viewBox="0 0 24 24" aria-hidden="true" ${attrs}>${body}</svg>`;

const f = (body: string): string => sv('fill="currentColor"', body);

const s = (body: string): string =>
  sv('fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"', body);

const BUTTON_DEFS: Partial<Record<MarkdownToolbarButtonName, MarkdownToolbarButton>> = {
  bold: {
    translateKey: 'btnBold',
    shortcut: 'Ctrl+B',
    icon: f(
      '<path d="M15.6 10.79c.97-.67 1.65-1.77 1.65-2.79 0-2.26-1.75-4-4-4H7v14h7.04c2.09 0 3.71-1.7 3.71-3.79 0-1.52-.86-2.82-2.15-3.42zM10 6.5h3c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5h-3v-3zm3.5 9H10v-3h3.5c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5z"/>'
    ),
  },
  italic: {
    translateKey: 'btnItalic',
    shortcut: 'Ctrl+I',
    icon: f('<path d="M10 4v3h2.21l-3.42 8H6v3h8v-3h-2.21l3.42-8H18V4z"/>'),
  },
  strikethrough: {
    translateKey: 'btnStrike',
    icon: s('<path d="M16 4H9a3 3 0 000 6h6a3 3 0 010 6H6"/><line x1="4" y1="12" x2="20" y2="12"/>'),
  },
  blockquote: {
    translateKey: 'btnQuote',
    icon: f('<path d="M6 17h3l2-4V7H5v6h3zm8 0h3l2-4V7h-6v6h3z"/>'),
  },
  bulletList: {
    translateKey: 'btnList',
    icon: s(
      '<line x1="9" y1="6" x2="20" y2="6"/><line x1="9" y1="12" x2="20" y2="12"/><line x1="9" y1="18" x2="20" y2="18"/><circle cx="4" cy="6" r="1.5" fill="currentColor" stroke="none"/><circle cx="4" cy="12" r="1.5" fill="currentColor" stroke="none"/><circle cx="4" cy="18" r="1.5" fill="currentColor" stroke="none"/>'
    ),
  },
  orderedList: {
    translateKey: 'btnListNumber',
    icon: f(
      '<path d="M2 17h2v.5H3v1h1v.5H2v1h3v-4H2v1zm1-9h1V4H2v1h1v3zm-1 3h1.8L2 13.1v.9h3v-1H3.2L5 10.9V10H2v1zm5-6v2h14V5H7zm0 14h14v-2H7v2zm0-6h14v-2H7v2z"/>'
    ),
  },
  table: {
    translateKey: 'btnTable',
    icon: s(
      '<rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="12" y1="3" x2="12" y2="21"/>'
    ),
  },
  link: {
    translateKey: 'btnLink',
    shortcut: 'Ctrl+K',
    icon: s(
      '<path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/>'
    ),
  },
  image: {
    translateKey: 'btnImage',
    icon: s(
      '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>'
    ),
  },
  code: {
    translateKey: 'btnCode',
    icon: s('<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>'),
  },
};

type DropdownName = 'heading' | 'keywords';

// ─── Composant ────────────────────────────────────────────────────────────────

export default defineComponent({
  name: 'MarkdownEditor',
  components: { InternalLink, MediathequeModale },

  props: {
    /** Identifiant renvoyé dans les events ; sert aussi d'id au textarea s'il est renseigné */
    meId: {
      type: String,
      default: '',
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
      type: Object as PropType<Partial<MarkdownEditorTranslate>>,
      default: () => ({}),
    },
    meKeyWords: {
      type: Array as PropType<MarkdownEditorKeyWord[]>,
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
    meRequired: {
      type: Boolean,
      default: false,
    },
  },

  emits: ['editor-value', 'editor-value-change'],

  setup(props, { emit }) {
    const editor = useEditor(props.meValue);
    const uid = useId();
    const { textareaRef, markdown, html, wordCount } = editor;

    const textareaId = computed<string>(() => props.meId || `md-editor-${uid}`);
    const isEmpty = computed<boolean>(() => markdown.value.trim() === '');
    const showRequiredError = computed<boolean>(() => props.meRequired && isEmpty.value);

    // Dernière valeur transmise via editor-value, évite les émissions en double au blur
    let lastEmittedValue = props.meValue;

    watch(markdown, (val) => {
      emit('editor-value-change', props.meId, val);
    });

    watch(
      () => props.meValue,
      (val) => {
        if (val !== markdown.value) {
          markdown.value = val;
          lastEmittedValue = val;
        }
      }
    );

    // ── Traductions ──────────────────────────────────────────────────────────

    function t(key: keyof MarkdownEditorTranslate, fallback: string = ''): string {
      const value = props.meTranslate?.[key];
      return typeof value === 'string' ? value : fallback;
    }

    function placeholder(key: keyof MarkdownEditorTranslate['placeholders'], fallback: string): string {
      return props.meTranslate?.placeholders?.[key] ?? fallback;
    }

    function buttonTitle(button: MarkdownToolbarButton): string {
      const label = t(button.translateKey, button.translateKey);
      return button.shortcut ? `${label} (${button.shortcut})` : label;
    }

    // ── Toolbar résolue ──────────────────────────────────────────────────────
    // 'save' est rendu séparément, après les modules custom, pour rester tout à droite

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

    const hasInternalLinkModule = computed<boolean>(() =>
      props.meModules.some((mod) => mod.name === InternalLinkModule.name)
    );
    const hasMediaModule = computed<boolean>(() => props.meModules.some((mod) => mod.name === MediaModule.name));

    // ── Urls des modales (chargées uniquement si un module en a besoin) ─────

    const urls = reactive<MarkdownEditorDatas>({ media: '', internalLinks: '' });

    function loadDatas(): void {
      if (!hasInternalLinkModule.value && !hasMediaModule.value) return;
      loadEditorDatas()
        .then((datas) => Object.assign(urls, datas))
        .catch((error) => console.error(error));
    }

    // ── Dropdowns ────────────────────────────────────────────────────────────

    const openDropdown = ref<DropdownName | null>(null);
    const toolbarRef = ref<HTMLElement | null>(null);

    function toggleDropdown(name: DropdownName): void {
      openDropdown.value = openDropdown.value === name ? null : name;
    }

    function onDocumentPointerDown(e: PointerEvent): void {
      if (openDropdown.value && !toolbarRef.value?.contains(e.target as Node)) {
        openDropdown.value = null;
      }
    }

    onMounted(() => {
      loadDatas();
      document.addEventListener('pointerdown', onDocumentPointerDown);
    });

    onUnmounted(() => {
      document.removeEventListener('pointerdown', onDocumentPointerDown);
    });

    // ── Actions toolbar ──────────────────────────────────────────────────────

    function insertTable(): void {
      const col = placeholder('tableColumn', 'Colonne');
      const cell = placeholder('tableCell', 'Cellule');
      editor.insertBlock(
        `| ${col} 1 | ${col} 2 | ${col} 3 |\n` + '|---|---|---|\n' + `| ${cell} | ${cell} | ${cell} |`
      );
    }

    function onButtonClick(name: MarkdownToolbarButtonName): void {
      switch (name) {
        case 'bold':
          editor.wrapSelection('**', '**', placeholder('bold', 'texte en gras'));
          break;
        case 'italic':
          editor.wrapSelection('*', '*', placeholder('italic', 'texte en italique'));
          break;
        case 'strikethrough':
          editor.wrapSelection('~~', '~~', placeholder('strike', 'texte barré'));
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
          editor.wrapSelection('`', '`', placeholder('code', 'code'));
          break;
        case 'link':
          editor.wrapSelection('[', '](url)', placeholder('link', 'Texte du lien'));
          break;
        case 'image':
          editor.wrapSelection('![', '](url)', placeholder('image', 'description'));
          break;
        case 'table':
          insertTable();
          break;
      }
    }

    function insertHeading(level: number): void {
      openDropdown.value = null;
      editor.insertLinePrefix('#'.repeat(level) + ' ');
    }

    function insertKeyword(keyword: string): void {
      openDropdown.value = null;
      editor.insertText(keyword);
    }

    function onInput(e: Event): void {
      markdown.value = (e.target as HTMLTextAreaElement).value;
    }

    function emitValue(): void {
      lastEmittedValue = markdown.value;
      emit('editor-value', props.meId, markdown.value);
    }

    function onBlur(): void {
      if (markdown.value !== lastEmittedValue) {
        emitValue();
      }
    }

    const editorApi: EditorApi = {
      editorId: uid,
      insertText: editor.insertText,
      wrapSelection: editor.wrapSelection,
      insertLinePrefix: editor.insertLinePrefix,
      insertBlock: editor.insertBlock,
      getSelection: editor.getSelection,
      focus: editor.focus,
      getMarkdown: editor.getMarkdown,
      setMarkdown: editor.setMarkdown,
    };

    function onModuleClick(module: EditorModule): void {
      module.action(editorApi);
    }

    function moduleTitle(module: EditorModule): string {
      return module.translateKey ? t(module.translateKey, module.label) : module.label;
    }

    // ── Raccourcis clavier ───────────────────────────────────────────────────

    // Échap puis Tab laisse sortir le focus du textarea (pas de piège clavier)
    let escapePressed = false;

    function handleKeydown(e: KeyboardEvent): void {
      if (e.key === 'Escape') {
        escapePressed = true;
        return;
      }
      if (e.key === 'Tab') {
        if (!escapePressed && !e.shiftKey && !e.ctrlKey && !e.altKey && !e.metaKey) {
          e.preventDefault();
          editor.insertText('  ');
        }
        escapePressed = false;
        return;
      }
      escapePressed = false;

      if (e.ctrlKey || e.metaKey) {
        const shortcuts: Record<string, MarkdownToolbarButtonName> = { b: 'bold', i: 'italic', k: 'link' };
        const name = shortcuts[e.key.toLowerCase()];
        if (name) {
          e.preventDefault();
          onButtonClick(name);
        }
      }
    }

    function onDropdownKeydown(e: KeyboardEvent): void {
      if (e.key === 'Escape' && openDropdown.value) {
        openDropdown.value = null;
        e.stopPropagation();
      }
    }

    const textareaStyle = computed(() => ({
      minHeight: `${props.meRows * 1.75}rem`,
    }));

    return {
      textareaRef,
      toolbarRef,
      markdown,
      uid,
      html,
      wordCount,
      textareaId,
      isEmpty,
      showRequiredError,
      urls,
      openDropdown,
      BUTTON_DEFS,
      resolvedGroups,
      hasSave,
      hasInternalLinkModule,
      hasMediaModule,
      textareaStyle,
      t,
      buttonTitle,
      moduleTitle,
      toggleDropdown,
      onButtonClick,
      insertHeading,
      insertKeyword,
      onInput,
      emitValue,
      onBlur,
      handleKeydown,
      onDropdownKeydown,
      onModuleClick,
    };
  },
});
</script>

<template>
  <div
    class="md-editor-wrap"
    :class="showRequiredError ? 'border-2 border-[var(--input-invalid)]' : 'border-1 border-[var(--border-color)]'"
  >
    <!-- ── TOOLBAR ─────────────────────────────────────── -->
    <div ref="toolbarRef" class="md-toolbar" role="toolbar" @keydown="onDropdownKeydown">
      <!-- 1. Groupes de boutons (save exclu) -->
      <template v-for="(group, gi) in resolvedGroups" :key="gi">
        <div v-if="gi > 0" class="tb-sep" />

        <template v-for="name in group" :key="name">
          <!-- Dropdown Titres -->
          <div v-if="name === 'heading'" class="relative">
            <button
              type="button"
              class="tb-btn no-control"
              style="padding: 0 0.625rem"
              :title="t('btnHeading', 'Titres')"
              :aria-label="t('btnHeading', 'Titres')"
              aria-haspopup="menu"
              :aria-expanded="openDropdown === 'heading'"
              @click="toggleDropdown('heading')"
            >
              H
              <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
              >
                <path d="M6 9l6 6 6-6" />
              </svg>
            </button>

            <div v-if="openDropdown === 'heading'" class="tb-dropdown-menu" style="min-width: 12rem">
              <ul class="py-1" role="menu">
                <li v-for="level in [1, 2, 3, 4, 5, 6]" :key="level" role="none">
                  <button
                    type="button"
                    role="menuitem"
                    class="tb-dropdown-item no-control"
                    @click="insertHeading(level)"
                  >
                    <span class="item-badge no-control">H{{ level }}</span>
                    <span class="item-label no-control" :class="`h-preview-${level}`">{{
                      t(`titreH${level}` as 'titreH1', `Titre ${level}`)
                    }}</span>
                    <span class="item-shortcut no-control">{{ '#'.repeat(level) + ' ' }}</span>
                  </button>
                </li>
              </ul>
            </div>
          </div>

          <!-- Dropdown Mots-clés -->
          <div v-else-if="name === 'keywords'" class="relative">
            <button
              type="button"
              class="tb-btn no-control"
              style="padding: 0 0.75rem"
              aria-haspopup="menu"
              :aria-expanded="openDropdown === 'keywords'"
              @click="toggleDropdown('keywords')"
            >
              <svg
                class="tb-icon"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
              >
                <path
                  d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"
                />
              </svg>
              {{ t('btnKeyWord') }}
              <svg
                class="tb-icon"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
              >
                <path d="M6 9l6 6 6-6" />
              </svg>
            </button>

            <div v-if="openDropdown === 'keywords'" class="tb-dropdown-menu w-max" style="min-width: 13rem">
              <ul class="py-1" role="menu">
                <li v-for="kw in meKeyWords" :key="kw.keyword" role="none">
                  <button
                    type="button"
                    role="menuitem"
                    class="tb-dropdown-item no-control"
                    @click="insertKeyword(kw.keyword)"
                  >
                    <span class="item-badge no-control">@</span>
                    <span class="item-label no-control">{{ kw.label }}</span>
                    <span class="item-shortcut no-control">{{ kw.keyword }}</span>
                  </button>
                </li>
              </ul>
            </div>
          </div>

          <!-- Boutons simples -->
          <button
            v-else-if="BUTTON_DEFS[name]"
            type="button"
            class="tb-btn"
            :title="buttonTitle(BUTTON_DEFS[name])"
            :aria-label="buttonTitle(BUTTON_DEFS[name])"
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
          type="button"
          class="tb-btn"
          :title="moduleTitle(mod)"
          :aria-label="moduleTitle(mod)"
          v-html="mod.icon"
          @click="onModuleClick(mod)"
        />
      </template>

      <!-- 3. Save — toujours en dernier grâce à margin-left: auto -->
      <button v-if="hasSave" type="button" class="tb-btn tb-btn-save" :title="t('btnSave')" @click="emitValue">
        <svg
          class="tb-icon"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
          aria-hidden="true"
        >
          <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
          <polyline points="17 21 17 13 7 13 7 21" />
          <polyline points="7 3 7 8 15 8" />
        </svg>
        {{ t('btnSave') }}
      </button>
    </div>

    <!-- ── TEXTAREA ──────────────────────────────────── -->
    <textarea
      :id="textareaId"
      ref="textareaRef"
      :value="markdown"
      class="md-textarea form-input"
      :placeholder="t('textareaPlaceholder')"
      :style="textareaStyle"
      :aria-invalid="showRequiredError"
      :aria-describedby="`md-footer-${uid}`"
      @input="onInput"
      @keydown="handleKeydown"
      @blur="onBlur"
    />

    <!-- ── FOOTER ─────────────────────────────────────── -->
    <div
      :id="`md-footer-${uid}`"
      class="md-footer-hint rounded-b-lg"
      :class="showRequiredError ? 'bg-[var(--error-bg)]' : ''"
    >
      <svg
        style="width: 0.875rem; height: 0.875rem; flex-shrink: 0"
        :class="showRequiredError ? 'text-[var(--error-text)]' : 'text-[var(--text-light)]'"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
      >
        <circle cx="12" cy="12" r="10" />
        <line x1="12" y1="8" x2="12" y2="12" />
        <line x1="12" y1="16" x2="12.01" y2="16" />
      </svg>
      <span v-if="showRequiredError" class="text-[var(--error-text)]">
        {{ t('msgEmptyContent') }}
      </span>
      <span v-else>
        {{ t('help') }}
        <a href="https://www.markdownguide.org" class="no-control" target="_blank" rel="noopener">Markdown</a>
      </span>

      <span class="md-footer-counts">
        {{ wordCount }} {{ t('words') }} · {{ markdown.length }} {{ t('caracteres') }}
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
        aria-hidden="true"
      >
        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
        <circle cx="12" cy="12" r="3" />
      </svg>
      <span>{{ t('preview') }}</span>
    </div>
    <div v-if="html" class="md-preview-body" v-html="html" />
    <div v-else class="md-preview-body">
      <p class="md-preview-empty">{{ t('emptyPreview') }}</p>
    </div>
  </div>

  <InternalLink
    v-if="hasInternalLinkModule"
    :editor-id="uid"
    :url="urls.internalLinks"
    :translate="meTranslate.modaleInternalLink"
  />
  <MediathequeModale
    v-if="hasMediaModule"
    :editor-id="uid"
    :url-media="urls.media"
    :translate="meTranslate.modaleMediatheque"
  />
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

.tb-dropdown-item {
  width: 100%;
  border: none;
  background: none;
  text-align: left;
}

.md-preview-body .md-preview-empty {
  color: var(--text-light);
  font-style: italic;
}
</style>
