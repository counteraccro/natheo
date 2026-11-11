<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Convertit un menu en ressource Menu de l'API v2
 */

namespace App\Api\V2\Mapper\Content;

use App\Api\V2\Output\Content\MenuElement;
use App\Api\V2\Resource\Content\Menu;
use App\Entity\Admin\Content\Menu\Menu as MenuEntity;
use App\Entity\Admin\Content\Menu\MenuElement as MenuElementEntity;
use App\Enum\Admin\Content\Menu\MenuPosition;
use App\Enum\Admin\Content\Menu\MenuType;
use App\Enum\Admin\Content\Page\PageCategory;

final readonly class MenuMapper
{
    public function __construct(private string $locale) {}

    /**
     * @param MenuEntity $entity
     * @return Menu
     */
    public function map(MenuEntity $entity): Menu
    {
        $menu = new Menu();
        $menu->id = (int) $entity->getId();
        $menu->position = MenuPosition::tryFrom((int) $entity->getPosition())?->getSlug() ?? '';
        $menu->type = MenuType::tryFrom((int) $entity->getType())?->getSlug() ?? '';
        $menu->elements = $this->mapElements(
            $entity->getMenuElements()->filter(fn(MenuElementEntity $element) => $element->getParent() === null),
        );
        return $menu;
    }

    /**
     * Éléments actifs, avec leurs enfants
     * @param iterable<MenuElementEntity> $elements
     * @return MenuElement[]
     */
    private function mapElements(iterable $elements): array
    {
        $return = [];
        foreach ($elements as $element) {
            // Lien vers une page non publiée : élément masqué avec ses enfants
            if ($element->isDisabled() || $element->getPage()?->isPublished() === false) {
                continue;
            }

            $translation = $element->getMenuElementTranslationByLocale($this->locale);
            $page = $element->getPage();
            $return[] = new MenuElement(
                (int) $element->getId(),
                (string) $translation?->getTextLink(),
                (string) $element->getLinkTarget(),
                $page === null ? ($translation?->getExternalLink() ?: null) : null,
                $page?->getPageTranslationByLocale($this->locale)?->getUrl(),
                $page === null ? null : PageCategory::tryFrom((int) $page->getCategory())?->getSlug(),
                (int) $element->getColumnPosition(),
                (int) $element->getRowPosition(),
                $this->mapElements($element->getChildren()),
            );
        }
        return $return;
    }
}
