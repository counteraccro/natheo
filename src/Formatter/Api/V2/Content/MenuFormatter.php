<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Convertit un menu (données de MenuRepository::getByIdForApi + éléments) en ressource Menu de l'API v2
 */

namespace App\Formatter\Api\V2\Content;

use App\Api\V2\Output\Content\MenuElement;
use App\Api\V2\Resource\Content\Menu;
use App\Entity\Admin\Content\Menu\MenuElement as MenuElementEntity;
use App\Enum\Admin\Content\Menu\MenuPosition;
use App\Enum\Admin\Content\Menu\MenuType;
use App\Enum\Admin\Content\Page\PageCategory;

final readonly class MenuFormatter
{
    public function __construct(private string $locale) {}

    /**
     * @param array{id: int, position: int, type: int, menuElements: MenuElementEntity[]} $data
     * @return Menu
     */
    public function format(array $data): Menu
    {
        $menu = new Menu();
        $menu->id = (int) $data['id'];
        $menu->position = MenuPosition::tryFrom((int) $data['position'])?->getApiName() ?? '';
        $menu->type = strtolower(MenuType::tryFrom((int) $data['type'])?->name ?? '');
        $menu->elements = $this->formatElements($data['menuElements']);
        return $menu;
    }

    /**
     * Éléments actifs, avec leurs enfants
     * @param iterable<MenuElementEntity> $elements
     * @return MenuElement[]
     */
    private function formatElements(iterable $elements): array
    {
        $return = [];
        foreach ($elements as $element) {
            if ($element->isDisabled()) {
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
                $this->formatElements($element->getChildren()),
            );
        }
        return $return;
    }
}
