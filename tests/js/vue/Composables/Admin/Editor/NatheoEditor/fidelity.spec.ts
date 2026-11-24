import { describe, expect, it } from 'vitest';
import { defineComponent, h, nextTick, reactive } from 'vue';
import { mount } from '@vue/test-utils';
import { EditorContent } from '@tiptap/vue-3';
import { modulesForPreset, natheoEditorModules } from '@/vue/Composables/Admin/Editor/NatheoEditor/natheoEditorModules';
import { markdownOf, useNatheoEditor } from '@/vue/Composables/Admin/Editor/NatheoEditor/useNatheoEditor';
import { coreEditor } from '@tests/vue/Components/Admin/Editors/NatheoEditor/Modules/support';
import { editorProps } from '@tests/vue/Composables/Admin/Editor/NatheoEditor/support';
import type { NatheoEditorState } from '@/ts/Admin/Editor/NatheoEditor/NatheoEditor.type';

/** Contenus Markdown des pages de démonstration */
const pages = import.meta.glob<string>('/src/DataFixtures/data/content/page/**/*.md', {
  eager: true,
  query: '?raw',
  import: 'default',
});

/** Fichiers YAML de démonstration qui contiennent du Markdown */
const yamlFiles = import.meta.glob<string>(
  [
    '/src/DataFixtures/data/content/faq/*.yaml',
    '/src/DataFixtures/data/content/comment/*.yaml',
    '/src/DataFixtures/data/system/mail_fixtures_data.yaml',
  ],
  { eager: true, query: '?raw', import: 'default' }
);

/**
 * Blocs littéraux (clé: |) et contenus entre guillemets (content: "…") d'un fichier YAML
 * @param yaml
 */
function markdownOfYaml(yaml: string): string[] {
  const found: string[] = [];
  const lines = yaml.split('\n');
  lines.forEach((line, index) => {
    const literal = /^(\s*)(?:-\s+)?[\w-]+:\s*\|\s*$/.exec(line);
    if (literal === null) return;
    const block: string[] = [];
    for (const next of lines.slice(index + 1)) {
      if (next.trim() !== '' && next.search(/\S/) <= literal[1].length) break;
      block.push(next);
    }
    const indent = Math.min(...block.filter((l) => l.trim() !== '').map((l) => l.search(/\S/)));
    found.push(
      block
        .map((l) => l.slice(indent))
        .join('\n')
        .replace(/\n+$/, '')
    );
  });
  for (const match of yaml.matchAll(/content:\s*"((?:[^"\\]|\\.)*)"/g)) {
    // Guillemets doubles YAML : un retour à la ligne vaut une espace, puis les échappements
    const folded = match[1].replace(/[ \t]*\n[ \t]*/g, ' ');
    found.push(
      folded.replace(/\\(.)/g, (_, char: string) => ({ n: '\n', t: '\t', '"': '"', '\\': '\\' })[char] ?? char)
    );
  }
  return found;
}

/** Cas limites que les fixtures n'ont pas */
const edgeCases: Record<string, string> = {
  'titres soulignés': 'Titre\n=====\n\nSous-titre\n----------',
  'gras et italique en _': '__gras__ et _penché_ et ***les deux***',
  'puces * et +': '* un\n* deux\n\n+ trois',
  'liste numérotée à partir de 3': '3. trois\n4. quatre',
  'listes imbriquées': '- a\n  - a1\n    - a2\n- b',
  'citation sur deux lignes': '> première\n> seconde\n\n> autre',
  'bloc de code indenté et avec accents graves': '    indenté\n\n````md\n```\ncode\n```\n````',
  'saut forcé par deux espaces et par \\': 'un  \ndeux\\\ntrois',
  'saut de ligne simple': 'un\ndeux\ntrois',
  'liens P#id et titre': '[page](P#12) et [site](https://natheo.fr "Titre") et <https://natheo.fr>',
  'adresse nue': 'Voir https://natheo.fr ou contact@natheo.fr',
  'image avec titre': '![Logo](/assets/logo.png "Logo")',
  'tableau aligné': '| Gauche | Centre | Droite |\n|:-------|:------:|-------:|\n| a | b | c |',
  'HTML brut': '<div class="encart">\n<p>Texte</p>\n</div>\n\nTexte <br> et <kbd>Ctrl</kbd>',
  attributs: '# Titre {.grand}\n\nParagraphe {#ancre}',
  'entités et caractères spéciaux': 'Prix&nbsp;: 10&nbsp;€ &copy; 2026, 5 * 3, a_b_c, \\*pas en italique\\*',
  'barré et code': '~~ancien~~ et `code avec * étoile`',
  'mots-clés dans un lien': 'Site [[[global.site_name]]]([[global.url]]) et [ici]([[global.url]])',
  séparateurs: 'Un\n\n***\n\nDeux\n\n___\n\nTrois',
  'liste espacée': '- a\n\n- b\n\n- c',
  'listes numérotées voisines': '1. a\n2. b\n\n1) c',
  'trois listes à puces voisines': '- a\n\n* b\n\n+ c',
  'citations imbriquées': '> un\n>\n> > deux',
  'liens par référence': '[Natheo][site] et [site]\n\n[site]: https://natheo.fr "Site"',
  'tableau avec mise en forme et barre échappée':
    '| A | B |\n|---|---|\n| **gras** | a \\| b |\n| `code` | [lien](/x) |',
  'titre fermé par des #': '## Titre ##',
  'code dans une liste': '- élément\n\n  ```php\n  echo 1;\n  ```',
  'paragraphe vide et espaces insécables': 'Un\n\n&nbsp;\n\nDeux',
};

const corpus: Record<string, string> = {
  ...Object.fromEntries(
    Object.entries(pages).map(([path, text]) => [path.replace('/src/DataFixtures/data/', ''), text])
  ),
  ...Object.fromEntries(
    Object.entries(yamlFiles).flatMap(([path, yaml]) =>
      markdownOfYaml(yaml).map((text, index) => [`${path.replace('/src/DataFixtures/data/', '')}#${index}`, text])
    )
  ),
  ...Object.fromEntries(Object.entries(edgeCases).map(([name, text]) => [`cas limite : ${name}`, text])),
};

/** Markdown réécrit par les modules du cœur */
const rewrite = (markdown: string): string => markdownOf(coreEditor(markdown), natheoEditorModules());

describe('fidélité de Natheo-Editor', () => {
  it('lit les contenus des fixtures', () => {
    expect(Object.keys(pages).length).toBeGreaterThan(50);
    expect(Object.keys(corpus).filter((name) => name.includes('.yaml#')).length).toBeGreaterThan(30);
  });

  it('émet le texte reçu tel quel tant que le document n’a pas changé', async () => {
    const modules = modulesForPreset(natheoEditorModules(), 'full');
    for (const [name, text] of Object.entries(corpus)) {
      const props = reactive(editorProps({ modelValue: text }));
      const emitted: string[] = [];
      let state!: NatheoEditorState;
      const Host = defineComponent({
        setup() {
          state = useNatheoEditor(props, (value) => emitted.push(value), modules);
          return () => h(EditorContent, { editor: state.editor.value });
        },
      });
      const wrapper = mount(Host);
      await nextTick();
      const editor = state.editor.value;
      editor.commands.insertContentAt(editor.state.doc.content.size, {
        type: 'paragraph',
        content: [{ type: 'text', text: 'X' }],
      });
      editor.commands.undo();
      expect(emitted[emitted.length - 1], name).toBe(text);
      wrapper.unmount();
    }
  });

  it('réécrit chaque contenu dans une forme fixe et stable', () => {
    for (const [name, text] of Object.entries(corpus)) {
      const after = rewrite(text);
      expect(rewrite(after), name).toBe(after);
    }
  });

  it('garde les paires avant / après comparées par le rendu du serveur', async () => {
    const pairs = Object.entries(corpus).map(([name, before]) => ({ name, before, after: rewrite(before) }));
    await expect(JSON.stringify(pairs, null, 2) + '\n').toMatchFileSnapshot(
      '../../../../../fixtures/natheo-editor/pairs.json'
    );
  });
});
