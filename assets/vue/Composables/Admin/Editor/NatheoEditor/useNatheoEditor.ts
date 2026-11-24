/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Moteur de Natheo-Editor : instance TipTap construite depuis les modules, Markdown émis (texte reçu rendu tel quel
 * tant que le document n'a pas changé), mode source, comptage des mots, lecture seule, hauteur, collage nettoyé
 */
import { computed, onBeforeUnmount, ref, shallowRef, watch } from 'vue';
import { Editor } from '@tiptap/vue-3';
import type { Editor as CoreEditor } from '@tiptap/core';
import { Markdown } from '@tiptap/markdown';
import { CharacterCount, Placeholder } from '@tiptap/extensions';
import { EditorState } from '@tiptap/pm/state';
import type { Node as ProseMirrorNode } from '@tiptap/pm/model';
import type { EditorView } from '@tiptap/pm/view';
import { cleanPastedHtml } from '@/vue/Composables/Admin/Editor/NatheoEditor/natheoEditorPaste';
import type { AdminEditorProps } from '@/ts/Admin/Editor/AdminEditor.type';
import type { NatheoEditorModule, NatheoEditorState } from '@/ts/Admin/Editor/NatheoEditor/NatheoEditor.type';

/** Hauteur d'une ligne du contenu, en em */
const LINE_HEIGHT = 1.65;

/**
 * Attributs de la zone de saisie : identifiant du champ, nom accessible, erreurs, langue
 * @param props
 */
export function editableAttributes(props: AdminEditorProps): Record<string, string> {
  const attributes: Record<string, string | undefined> = {
    id: props.id,
    'aria-labelledby': props.labelledBy,
    'aria-label': props.labelledBy ? undefined : props.label,
    'aria-describedby': props.describedBy,
    'aria-invalid': props.invalid ? 'true' : undefined,
    'aria-required': props.required ? 'true' : undefined,
    'aria-readonly': props.readonly ? 'true' : undefined,
    'aria-disabled': props.disabled ? 'true' : undefined,
    'aria-multiline': 'true',
    lang: props.lang,
    class: 'natheo-editor-content',
  };
  return Object.fromEntries(
    Object.entries(attributes).filter((entry): entry is [string, string] => entry[1] !== undefined)
  );
}

/**
 * Mots d'un texte Markdown, la syntaxe seule (#, -, |) n'en étant pas un
 * @param markdown
 */
export function countWords(markdown: string): number {
  return markdown.split(/\s+/).filter((word) => /[\p{L}\p{N}]/u.test(word)).length;
}

/**
 * Markdown du document, retouché par les modules
 * @param editor
 * @param modules
 */
export function markdownOf(editor: Editor | CoreEditor, modules: NatheoEditorModule[]): string {
  return modules.reduce((markdown, module) => module.transformMarkdown?.(markdown) ?? markdown, editor.getMarkdown());
}

/**
 * Crée l'éditeur et le détruit avec le composant
 * @param props props du contrat, réactives
 * @param emit émet le Markdown modifié
 * @param modules modules retenus pour le préréglage
 */
export function useNatheoEditor(
  props: AdminEditorProps,
  emit: (value: string) => void,
  modules: NatheoEditorModule[]
): NatheoEditorState {
  const editable = computed(() => !props.readonly && !props.disabled);
  const words = ref(0);

  // Texte reçu et document lu depuis celui-ci : rendu tel quel tant que le document est identique
  let original = props.modelValue;
  let originalDoc!: ProseMirrorNode;
  let emitted = props.modelValue;

  const valueOf = (editor: Editor): string =>
    editor.state.doc.eq(originalDoc) ? original : markdownOf(editor, modules);

  // Style du texte : celui du back-office, ou celui du thème du site (classe et data-front-theme)
  const contentAttributes = ref<Record<string, string>>({ class: 'natheo-editor-admin' });
  const attributes = (): Record<string, string> => {
    const own = editableAttributes(props);
    return { ...contentAttributes.value, ...own, class: `${own.class} ${contentAttributes.value.class ?? ''}`.trim() };
  };

  const editorProps = () => ({
    attributes: attributes(),
    transformPastedHTML: cleanPastedHtml,
    handlePaste: (_view: EditorView, event: ClipboardEvent): boolean => pasteMarkdown(event),
  });

  // Texte collé sans HTML : lu comme du Markdown, sauf dans un bloc de code
  const pasteMarkdown = (event: ClipboardEvent): boolean => {
    const text = event.clipboardData?.getData('text/plain') ?? '';
    if (text === '' || event.clipboardData?.getData('text/html') || editor.value.isActive('codeBlock')) {
      return false;
    }
    return editor.value.commands.insertContent(text, { contentType: 'markdown' });
  };

  const editor = shallowRef(
    new Editor({
      extensions: [
        Markdown,
        CharacterCount,
        Placeholder.configure({ placeholder: () => props.placeholder ?? '' }),
        ...modules.flatMap((module) => module.extensions({ props })),
      ],
      content: props.modelValue,
      contentType: 'markdown',
      editable: editable.value,
      editorProps: editorProps(),
      onUpdate: ({ editor: updated }) => {
        words.value = updated.storage.characterCount.words();
        const value = valueOf(updated as Editor);
        if (value !== emitted) {
          emitted = value;
          emit(value);
        }
      },
    })
  );
  originalDoc = editor.value.state.doc;
  words.value = editor.value.storage.characterCount.words();

  /**
   * Remplace le texte sans l'émettre : document relu, historique d'annulation repris de zéro
   * @param value
   */
  const replaceContent = (value: string): void => {
    original = value;
    emitted = value;
    const current = editor.value;
    current.commands.setContent(value, { contentType: 'markdown', emitUpdate: false });
    current.view.updateState(EditorState.create({ doc: current.state.doc, plugins: current.state.plugins }));
    originalDoc = current.state.doc;
    words.value = current.storage.characterCount.words();
  };

  // Mode source : le texte de la zone est la valeur émise tant qu'elle est ouverte
  const source = ref(false);
  const sourceText = ref('');

  /**
   * Ouvre le mode source sur la valeur courante, ou revient à l'éditeur en relisant le texte
   */
  const toggleSource = (): void => {
    if (source.value) {
      replaceContent(sourceText.value);
    } else {
      sourceText.value = valueOf(editor.value);
    }
    source.value = !source.value;
  };

  /**
   * Texte saisi dans le mode source
   * @param value
   */
  const setSourceText = (value: string): void => {
    if (!source.value || !editable.value || value === sourceText.value) return;
    sourceText.value = value;
    words.value = countWords(value);
    emitted = value;
    emit(value);
  };

  // Valeur remplacée par le formulaire (rechargement, conflit)
  watch(
    () => props.modelValue,
    (value) => {
      if (value === emitted) return;
      replaceContent(value);
      if (source.value) sourceText.value = value;
    }
  );

  watch(editable, (value) => editor.value.setEditable(value, false));
  watch(attributes, () => editor.value.setOptions({ editorProps: editorProps() }), { deep: true });

  onBeforeUnmount(() => editor.value.destroy());

  return {
    editor,
    modules,
    words,
    editable,
    replaceContent,
    source,
    sourceText,
    toggleSource,
    setSourceText,
    contentAttributes,
    height: computed(() => ({ minHeight: `${props.rows * LINE_HEIGHT}em`, maxHeight: '60vh' })),
  };
}
