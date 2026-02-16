/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Composable cœur de l'éditeur Markdown
 */

import { ref, computed, type Ref, type ComputedRef } from 'vue';
import { marked } from 'marked';
import DOMPurify from 'dompurify';
import type { UseEditorReturn, EditorSelection } from '@/ts/MarkdownEditor/MarkdownEditor.types';

// ─── Renderer Nathéo ────────────────────────────────────────────────────────

const renderer = new marked.Renderer();

renderer.heading = (text: string, level: number): string => {
  const sizes: Record<number, string> = {
    1: 'text-3xl font-bold',
    2: 'text-2xl font-bold',
    3: 'text-xl font-semibold',
    4: 'text-lg font-semibold',
    5: 'text-base font-semibold',
    6: 'text-sm font-semibold',
  };
  return `<h${level} class="${sizes[level] ?? ''} my-4" style="color:var(--text-primary)">${text}</h${level}>`;
};

renderer.paragraph = (text: string): string =>
  `<p class="mb-4 leading-relaxed" style="color:var(--text-primary)">${text}</p>`;

renderer.strong = (text: string): string => `<strong class="font-bold">${text}</strong>`;

renderer.em = (text: string): string => `<em class="italic">${text}</em>`;

renderer.del = (text: string): string => `<del class="line-through opacity-60">${text}</del>`;

renderer.blockquote = (text: string): string =>
  `<blockquote class="border-l-4 pl-4 my-4 italic rounded-r-lg py-2 pr-4"
    style="border-color:var(--primary);color:var(--text-secondary);background:var(--bg-hover)">${text}</blockquote>`;

renderer.code = (code: string, lang?: string): string =>
  `<pre class="rounded-lg p-4 my-4 overflow-x-auto text-sm font-mono"
    style="background:var(--bg-hover);border:1px solid var(--border-color)"><code class="language-${lang ?? ''}">${code}</code></pre>`;

renderer.codespan = (text: string): string =>
  `<code class="px-1.5 py-0.5 rounded text-sm font-mono"
    style="background:var(--primary-lighter);color:var(--primary);border:1px solid var(--border-color)">${text}</code>`;

renderer.list = (body: string, ordered: boolean): string => {
  const tag = ordered ? 'ol' : 'ul';
  const cls = ordered ? 'list-decimal' : 'list-disc';
  return `<${tag} class="${cls} pl-6 mb-4 space-y-1" style="color:var(--text-primary)">${body}</${tag}>`;
};

renderer.listitem = (text: string): string => `<li class="leading-relaxed">${text}</li>`;

renderer.link = (href: string, _title: string | null, text: string): string =>
  `<a href="${href}" class="underline underline-offset-2" style="color:var(--primary)"
    target="_blank" rel="noopener noreferrer">${text}</a>`;

renderer.image = (href: string, _title: string | null, text: string): string =>
  `<img src="${href}" alt="${text}" class="rounded-lg max-w-full my-4"
    style="border:1px solid var(--border-color)">`;

renderer.hr = (): string => `<hr class="my-6" style="border-color:var(--border-color)">`;

renderer.table = (header: string, body: string): string =>
  `<div class="overflow-x-auto my-4">
    <table class="w-full text-sm" style="border:1px solid var(--border-color);border-collapse:collapse">
      <thead style="background:var(--bg-hover)">${header}</thead>
      <tbody>${body}</tbody>
    </table>
  </div>`;

renderer.tablerow = (content: string): string =>
  `<tr style="border-bottom:1px solid var(--border-color)">${content}</tr>`;

renderer.tablecell = (
  content: string,
  flags: { header: boolean; align: 'center' | 'left' | 'right' | null }
): string => {
  const tag = flags.header ? 'th' : 'td';
  const cls = flags.header ? 'px-4 py-2 font-semibold text-left' : 'px-4 py-2';
  const style = flags.align ? ` style="text-align:${flags.align}"` : '';
  return `<${tag} class="${cls}"${style}>${content}</${tag}>`;
};

marked.use({ renderer, breaks: true, gfm: true });

// ─── Composable ──────────────────────────────────────────────────────────────

export function useEditor(initialValue: string = ''): UseEditorReturn {
  const textareaRef = ref<HTMLTextAreaElement | null>(null);
  const markdown = ref<string>(initialValue);

  // ── Rendu ─────────────────────────────────────────────────────────────────
  const html: ComputedRef<string> = computed(() => {
    if (!markdown.value.trim()) return '';
    return DOMPurify.sanitize(marked.parse(markdown.value) as string, {
      ADD_ATTR: ['target', 'rel', 'class', 'style'],
    });
  });

  const wordCount: ComputedRef<number> = computed(() =>
    markdown.value.trim() ? markdown.value.trim().split(/\s+/).length : 0
  );

  // ── Helpers curseur ────────────────────────────────────────────────────────
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

  // ── API publique ──────────────────────────────────────────────────────────
  function insertText(text: string): void {
    const el = textareaRef.value;
    if (!el) return;
    focus();

    const start = el.selectionStart;
    const end = el.selectionEnd;
    const before = markdown.value.substring(0, start);
    const after = markdown.value.substring(end);

    markdown.value = before + text + after;

    const newPos = start + text.length;
    setTimeout(() => {
      if (!textareaRef.value) return;
      textareaRef.value.selectionStart = newPos;
      textareaRef.value.selectionEnd = newPos;
      focus();
    }, 0);
  }

  function wrapSelection(before: string, after: string, placeholder: string = ''): void {
    const el = textareaRef.value;
    if (!el) return;
    focus();

    const { text, start, end } = getSelection();
    const selected = text || placeholder;
    const wrapped = before + selected + after;

    markdown.value = markdown.value.substring(0, start) + wrapped + markdown.value.substring(end);

    setTimeout(() => {
      if (!textareaRef.value) return;
      if (text) {
        textareaRef.value.selectionStart = start + before.length;
        textareaRef.value.selectionEnd = start + before.length + text.length;
      } else {
        textareaRef.value.selectionStart = start + before.length;
        textareaRef.value.selectionEnd = start + before.length + placeholder.length;
      }
      focus();
    }, 0);
  }

  function insertLinePrefix(prefix: string): void {
    const el = textareaRef.value;
    if (!el) return;
    focus();

    const pos = el.selectionStart;
    const lineStart = markdown.value.lastIndexOf('\n', pos - 1) + 1;
    const before = markdown.value.substring(0, lineStart);
    const after = markdown.value.substring(lineStart);

    markdown.value = before + prefix + after;

    const newPos = lineStart + prefix.length;
    setTimeout(() => {
      if (!textareaRef.value) return;
      textareaRef.value.selectionStart = newPos;
      textareaRef.value.selectionEnd = newPos;
      focus();
    }, 0);
  }

  function insertBlock(text: string): void {
    const el = textareaRef.value;
    if (!el) return;
    const needsNewline = el.selectionStart > 0 && markdown.value[el.selectionStart - 1] !== '\n';
    insertText((needsNewline ? '\n' : '') + text + '\n');
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
