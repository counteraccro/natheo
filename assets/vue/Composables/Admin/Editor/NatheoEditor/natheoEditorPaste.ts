/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Nettoyage du HTML collé dans Natheo-Editor (Word, page web) : éléments connus gardés, styles et attributs retirés
 */
import { isAllowedImage, isAllowedLink } from '@/vue/Composables/Admin/Editor/NatheoEditor/natheoEditorUrls';

/** Éléments retirés avec leur contenu */
const DROPPED =
  'script,style,meta,link,title,template,iframe,object,embed,svg,math,form,input,button,select,textarea,noscript';

/** Attributs gardés par élément, tous les autres sont retirés */
const KEPT_ATTRIBUTES: Record<string, string[]> = {
  A: ['href', 'title'],
  IMG: ['src', 'alt', 'title'],
  TD: ['colspan', 'rowspan'],
  TH: ['colspan', 'rowspan'],
  OL: ['start'],
};

/** Classe de langue d'un bloc de code */
const LANGUAGE_CLASS = /^language-[\w+-]+$/;

/**
 * Remplace un élément par son contenu
 * @param element
 */
function unwrap(element: Element): void {
  element.replaceWith(...Array.from(element.childNodes));
}

/**
 * Entoure le contenu d'un élément d'une balise de mise en forme
 * @param element
 * @param tag
 */
function wrapContent(element: Element, tag: string): void {
  const wrapper = element.ownerDocument.createElement(tag);
  wrapper.append(...Array.from(element.childNodes));
  element.append(wrapper);
}

/**
 * Gras et italique donnés par un style (Google Docs, Word) changés en balises avant le retrait des styles
 * @param element
 */
function keepStyledMarks(element: HTMLElement): void {
  const weight = element.style.fontWeight;
  const bold = weight === 'bold' || weight === 'bolder' || Number(weight) >= 600;
  if (['B', 'STRONG'].includes(element.tagName) && (weight === 'normal' || weight === '400')) {
    unwrap(element);
    return;
  }
  if (element.tagName === 'SPAN') {
    if (bold) wrapContent(element, 'strong');
    if (element.style.fontStyle === 'italic') wrapContent(element, 'em');
  }
}

/**
 * Attributs non gardés retirés, adresses refusées supprimées
 * @param element
 */
function cleanAttributes(element: Element): void {
  const kept = KEPT_ATTRIBUTES[element.tagName] ?? [];
  for (const { name, value } of Array.from(element.attributes)) {
    const isLanguage =
      element.tagName === 'CODE' && name === 'class' && value.split(/\s+/).some((c) => LANGUAGE_CLASS.test(c));
    if (!kept.includes(name) && !isLanguage) element.removeAttribute(name);
  }
  if (element.tagName === 'CODE' && element.hasAttribute('class')) {
    element.setAttribute(
      'class',
      element
        .getAttribute('class')!
        .split(/\s+/)
        .filter((c) => LANGUAGE_CLASS.test(c))
        .join(' ')
    );
  }
  if (element.tagName === 'A' && !isAllowedLink(element.getAttribute('href') ?? '')) {
    element.removeAttribute('href');
  }
}

/** Attribut posé par ProseMirror sur la racine d'une copie : profondeurs ouvertes puis contexte */
const PM_SLICE = /^\d+ \d+(?: |$)/;

/**
 * Copie depuis un éditeur ProseMirror : un élément racine porte data-pm-slice
 * @param body
 */
function isEditorCopy(body: HTMLElement): boolean {
  return Array.from(body.children).some((child) => PM_SLICE.test(child.getAttribute('data-pm-slice') ?? ''));
}

/**
 * HTML collé réduit aux éléments et attributs que les modules savent lire ; une copie depuis un éditeur
 * (data-pm-slice) est gardée telle quelle
 * @param html
 */
export function cleanPastedHtml(html: string): string {
  const body = new DOMParser().parseFromString(html, 'text/html').body;
  if (isEditorCopy(body)) {
    return html;
  }

  const walker = body.ownerDocument.createTreeWalker(body, NodeFilter.SHOW_COMMENT);
  const comments: Node[] = [];
  while (walker.nextNode()) comments.push(walker.currentNode);
  comments.forEach((comment) => comment.parentNode?.removeChild(comment));

  body.querySelectorAll(DROPPED).forEach((element) => element.remove());
  for (const element of Array.from(body.querySelectorAll('*'))) {
    if (!element.isConnected) continue;
    // Balises Office (o:p, w:sdt…) : sans équivalent
    if (element.tagName.includes(':')) {
      element.remove();
      continue;
    }
    if (element.tagName === 'IMG' && !isAllowedImage(element.getAttribute('src') ?? '')) {
      element.remove();
      continue;
    }
    keepStyledMarks(element as HTMLElement);
  }
  body.querySelectorAll('*').forEach(cleanAttributes);
  body.querySelectorAll('span,font').forEach(unwrap);
  return body.innerHTML;
}
