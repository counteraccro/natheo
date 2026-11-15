/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Conversion du menu en cours d'édition en ressource Menu de l'API v2, pour l'aperçu au design du thème :
 * élément désactivé ou lien vers une page non publiée masqué avec ses enfants
 */
import type { MenuElement } from '@/ts/Menu/Menu.type';
import type { Menu as ApiMenu, MenuElement as ApiMenuElement } from '@/ts/Front/Api/Menu.type';
import type { MenuPreviewInput } from '@/ts/Menu/MenuPreview.type';

/**
 * Menu au format de l'API v2, null tant que son type ou sa position n'est pas connu
 */
export function toApiMenu(source: MenuPreviewInput): ApiMenu | null {
  const type = source.slugs.types[String(source.menu.type)];
  const position = source.slugs.positions[String(source.menu.position)];
  if (type === undefined || position === undefined) {
    return null;
  }

  return {
    '@id': '',
    '@type': 'Menu',
    id: source.menu.id ?? 0,
    type,
    position,
    elements: toApiElements(source.menu.menuElements ?? [], source),
  };
}

function toApiElements(elements: MenuElement[], source: MenuPreviewInput): ApiMenuElement[] {
  return [...elements]
    .sort((a, b) => a.columnPosition - b.columnPosition || a.rowPosition - b.rowPosition)
    .flatMap((element) => {
      const pageId = Number(element.page);
      const hasPage = pageId > 0;
      const previewPage = hasPage ? source.previewPages[String(pageId)] : undefined;
      if (element.disabled || (hasPage && previewPage?.published !== true)) {
        return [];
      }

      const translation = element.menuElementTranslations.find((item) => item.locale === source.locale);
      return [
        {
          id: element.id,
          label: translation?.textLink ?? '',
          target: element.linkTarget,
          url: hasPage ? null : translation?.externalLink || null,
          pageSlug: hasPage ? (source.pages[String(pageId)]?.[source.locale]?.url ?? null) : null,
          pageCategory: previewPage?.category ?? null,
          column: element.columnPosition,
          row: element.rowPosition,
          children: toApiElements(element.children ?? [], source),
        },
      ];
    });
}
