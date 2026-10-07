/**
 * @author Gourdon Aymeric
 * @version 3.0
 * Composable cœur de l'éditeur Markdown
 */

import { ref, computed, type ComputedRef } from 'vue';
import axios from 'axios';
import { Marked, Renderer, type RendererObject } from 'marked';
import { renderMarkdown } from '@/ts/MarkdownEditor/markdownRender';
import type { UseEditorReturn, EditorSelection, MarkdownEditorDatas } from '@/ts/MarkdownEditor/MarkdownEditor.type';

// ─── Renderer Nathéo (preview admin) ─────────────────────────────────────────

const HEADING_SIZES: Record<number, string> = {
  1: 'text-3xl font-bold',
  2: 'text-2xl font-bold',
  3: 'text-xl font-semibold',
  4: 'text-lg font-semibold',
  5: 'text-base font-semibold',
  6: 'text-sm font-semibold',
};

/** Ajoute des attributs à la balise ouvrante `tag` en début de html */
const withAttrs = (html: string, tag: string, attrs: string): string =>
  html.replace(new RegExp(`^<${tag}(?=[\\s>])`), `<${tag} ${attrs}`);

// Chaque méthode réutilise le rendu par défaut de marked et n'ajoute que le style
const renderer: RendererObject = {
  heading(token) {
    const html = Renderer.prototype.heading.call(this, token);
    return withAttrs(
      html,
      `h${token.depth}`,
      `class="${HEADING_SIZES[token.depth] ?? ''} my-4" style="color:var(--text-primary)"`
    );
  },
  paragraph(token) {
    return withAttrs(
      Renderer.prototype.paragraph.call(this, token),
      'p',
      'class="mb-4 leading-relaxed" style="color:var(--text-primary)"'
    );
  },
  strong(token) {
    return withAttrs(Renderer.prototype.strong.call(this, token), 'strong', 'class="font-bold"');
  },
  em(token) {
    return withAttrs(Renderer.prototype.em.call(this, token), 'em', 'class="italic"');
  },
  del(token) {
    return withAttrs(Renderer.prototype.del.call(this, token), 'del', 'class="line-through opacity-60"');
  },
  blockquote(token) {
    return withAttrs(
      Renderer.prototype.blockquote.call(this, token),
      'blockquote',
      'class="border-l-4 pl-4 my-4 italic rounded-r-lg py-2 pr-4" style="border-color:var(--primary);color:var(--text-secondary);background:var(--bg-hover)"'
    );
  },
  code(token) {
    return withAttrs(
      Renderer.prototype.code.call(this, token),
      'pre',
      'class="rounded-lg p-4 my-4 overflow-x-auto text-sm font-mono" style="background:var(--bg-hover);border:1px solid var(--border-color)"'
    );
  },
  codespan(token) {
    return withAttrs(
      Renderer.prototype.codespan.call(this, token),
      'code',
      'class="px-1.5 py-0.5 rounded text-sm font-mono" style="background:var(--primary-lighter);color:var(--primary);border:1px solid var(--border-color)"'
    );
  },
  list(token) {
    const tag = token.ordered ? 'ol' : 'ul';
    const cls = token.ordered ? 'list-decimal' : 'list-disc';
    return withAttrs(
      Renderer.prototype.list.call(this, token),
      tag,
      `class="${cls} pl-6 mb-4 space-y-1" style="color:var(--text-primary)"`
    );
  },
  listitem(item) {
    return withAttrs(Renderer.prototype.listitem.call(this, item), 'li', 'class="leading-relaxed"');
  },
  link(token) {
    // Les liens internes "P#id" ne sont résolus que côté serveur
    if (/^P#\d+$/.test(token.href)) {
      const text = this.parser.parseInline(token.tokens);
      return `<span class="underline decoration-dotted underline-offset-2" style="color:var(--primary)" title="${token.href}">${text}</span>`;
    }
    return withAttrs(
      Renderer.prototype.link.call(this, token),
      'a',
      'class="underline underline-offset-2" style="color:var(--primary)" target="_blank" rel="noopener noreferrer"'
    );
  },
  image(token) {
    return withAttrs(
      Renderer.prototype.image.call(this, token),
      'img',
      'class="rounded-lg max-w-full my-4" style="border:1px solid var(--border-color)"'
    );
  },
  hr(token) {
    return withAttrs(
      Renderer.prototype.hr.call(this, token),
      'hr',
      'class="my-6" style="border-color:var(--border-color)"'
    );
  },
  table(token) {
    const html = withAttrs(
      Renderer.prototype.table.call(this, token),
      'table',
      'class="w-full text-sm" style="border:1px solid var(--border-color);border-collapse:collapse"'
    ).replace('<thead>', '<thead style="background:var(--bg-hover)">');
    return `<div class="overflow-x-auto my-4">${html}</div>`;
  },
  tablerow(token) {
    return withAttrs(
      Renderer.prototype.tablerow.call(this, token),
      'tr',
      'style="border-bottom:1px solid var(--border-color)"'
    );
  },
  tablecell(token) {
    const tag = token.header ? 'th' : 'td';
    let attrs = token.header ? 'class="px-4 py-2 font-semibold"' : 'class="px-4 py-2"';
    attrs += ` style="text-align:${token.align ?? 'left'}"`;
    return withAttrs(Renderer.prototype.tablecell.call(this, token), tag, attrs);
  },
};

const editorParser = new Marked({ renderer, breaks: true, gfm: true });

// ─── Helpers markdown ────────────────────────────────────────────────────────

/** Échappe les caractères qui casseraient le texte d'un lien markdown [texte] */
export function escapeLinkText(text: string): string {
  return text.replace(/([\\[\]])/g, '\\$1');
}

/** Formate une url pour un lien markdown : <url> si elle contient espaces ou parenthèses */
export function formatLinkUrl(url: string): string {
  if (!/[\s()<>]/.test(url)) return url;
  return `<${url.replace(/</g, '%3C').replace(/>/g, '%3E')}>`;
}

/** Familles de préfixes de ligne : un nouveau préfixe remplace celui de la même famille */
const LINE_PREFIX_FAMILIES: RegExp[] = [/^#{1,6} /, /^> /, /^(?:[-*+]|\d+\.) /];

// ─── Données serveur partagées entre toutes les instances ────────────────────

let datasPromise: Promise<MarkdownEditorDatas> | null = null;

/**
 * Charge (une seule fois par page) les urls nécessaires aux modules de l'éditeur
 */
export function loadEditorDatas(): Promise<MarkdownEditorDatas> {
  if (datasPromise === null) {
    const locale = document.documentElement.lang || 'fr';
    datasPromise = axios
      .get<MarkdownEditorDatas>(`/admin/${locale}/markdown/ajax/load-datas`)
      .then((response) => response.data)
      .catch((error) => {
        datasPromise = null;
        throw error;
      });
  }
  return datasPromise;
}

// ─── Composable ──────────────────────────────────────────────────────────────

export function useEditor(initialValue: string = ''): UseEditorReturn {
  const textareaRef = ref<HTMLTextAreaElement | null>(null);
  const markdown = ref<string>(initialValue);

  const html: ComputedRef<string> = computed(() => renderMarkdown(markdown.value, editorParser, true));

  const wordCount: ComputedRef<number> = computed(() =>
    markdown.value.trim() ? markdown.value.trim().split(/\s+/).length : 0
  );

  function focus(): void {
    textareaRef.value?.focus();
  }

  function getSelection(): EditorSelection {
    const el = textareaRef.value;
    if (!el) return { text: '', start: 0, end: 0 };
    return {
      text: el.value.substring(el.selectionStart, el.selectionEnd),
      start: el.selectionStart,
      end: el.selectionEnd,
    };
  }

  /**
   * Remplace [start, end] par text puis positionne la sélection
   */
  function replaceRange(start: number, end: number, text: string, selStart: number, selEnd: number): void {
    const el = textareaRef.value;
    if (!el) return;
    el.focus();
    el.setSelectionRange(start, end);
    // execCommand conserve l'historique natif (Ctrl+Z), setRangeText sert de repli
    if (!document.execCommand('insertText', false, text)) {
      el.setRangeText(text, start, end, 'end');
    }
    markdown.value = el.value;
    el.setSelectionRange(selStart, selEnd);
  }

  function insertText(text: string): void {
    const { start, end } = getSelection();
    const pos = start + text.length;
    replaceRange(start, end, text, pos, pos);
  }

  function wrapSelection(before: string, after: string, placeholder: string = ''): void {
    const { text, start, end } = getSelection();
    const inner = text || placeholder;
    const innerStart = start + before.length;
    replaceRange(start, end, before + inner + after, innerStart, innerStart + inner.length);
  }

  function insertLinePrefix(prefix: string): void {
    if (!textareaRef.value) return;
    const value = markdown.value;
    const { start, end } = getSelection();

    const blockStart = value.lastIndexOf('\n', start - 1) + 1;
    // Une sélection finissant par un saut de ligne n'englobe pas la ligne suivante
    const lastPos = end > start && value[end - 1] === '\n' ? end - 1 : end;
    const newLine = value.indexOf('\n', lastPos);
    const blockEnd = newLine === -1 ? value.length : newLine;

    const lines = value.substring(blockStart, blockEnd).split('\n');
    const ordered = /^\d+\. $/.test(prefix);
    const family = LINE_PREFIX_FAMILIES.find((re) => re.test(prefix));
    const strip = (line: string): string => (family ? line.replace(family, '') : line);

    const alreadyPrefixed = lines.every((line) => (ordered ? /^\d+\. /.test(line) : line.startsWith(prefix)));
    const newBlock = lines
      .map((line, i) => (alreadyPrefixed ? strip(line) : (ordered ? `${i + 1}. ` : prefix) + strip(line)))
      .join('\n');

    if (lines.length === 1 && start === end) {
      const pos = Math.max(blockStart, start + newBlock.length - (blockEnd - blockStart));
      replaceRange(blockStart, blockEnd, newBlock, pos, pos);
    } else {
      replaceRange(blockStart, blockEnd, newBlock, blockStart, blockStart + newBlock.length);
    }
  }

  function insertBlock(text: string): void {
    const { start, end } = getSelection();
    const head = markdown.value.substring(0, start);
    const tail = markdown.value.substring(end);

    // Ligne vide autour du bloc : "texte\n---" serait sinon lu comme un titre
    let before = '\n\n';
    if (head === '' || head.endsWith('\n\n')) before = '';
    else if (head.endsWith('\n')) before = '\n';

    let after = '\n\n';
    if (tail === '') after = '\n';
    else if (tail.startsWith('\n\n')) after = '';
    else if (tail.startsWith('\n')) after = '\n';

    insertText(before + text + after);
  }

  function getMarkdown(): string {
    return markdown.value;
  }

  function setMarkdown(value: string): void {
    markdown.value = value;
  }

  return {
    textareaRef,
    markdown,
    html,
    wordCount,
    focus,
    getSelection,
    insertText,
    wrapSelection,
    insertLinePrefix,
    insertBlock,
    getMarkdown,
    setMarkdown,
  };
}
