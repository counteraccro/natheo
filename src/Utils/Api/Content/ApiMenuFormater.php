<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Converti un menu en array pour le retour API
 */

namespace App\Utils\Api\Content;

use App\Entity\Admin\Content\Menu\MenuElement;
use App\Utils\Content\Menu\MenuConst;
use App\Utils\System\Options\OptionSystemKey;

class ApiMenuFormater
{

    /**
     * @var array
     */
    private array $return = [];

    /**
     * @param array $menu
     * @param string $locale
     * @param array $apiOptionSystem
     * @param array $apiData
     */
    public function __construct(
        private readonly array  $menu,
        private readonly string $locale,
        private readonly array  $apiOptionSystem,
        private readonly array  $apiData,
    )
    {
    }

    /**
     * Converti un menu en array pour API
     * @return $this
     */
    public function convertMenu(): static
    {
        $this->return['position'] = $this->getStringPosition($this->menu['position']);
        $this->return['type'] = $this->menu['type'];
        $this->return['elements'] = $this->getElements($this->menu['menuElements']);
        return $this;
    }

    /**
     * Génères un array avec les elements
     * @param array $elements
     * @return array
     */
    private function getElements(array $elements): array
    {
        $return = [];
        $i = 0;
        foreach ($elements as $element) {
            /** @var MenuElement $element */

            if ($element->isDisabled() === true) {
                continue;
            }

            $elementTranslation = $element->getMenuElementTranslationByLocale($this->locale);

            if ($element->getPage() !== null) {
                $url = $element->getPage()->getPageTranslationByLocale($this->locale)->getUrl();
                $category = strtolower($this->apiData['pageCategories'][$element->getPage()->getCategory()]);
                $url = $this->apiOptionSystem[OptionSystemKey::OS_ADRESSE_SITE] . '/' . $category . '/' . $url;
            } else {
                $url = $elementTranslation->getExternalLink();
            }

            $return[$i] = [
                'target' => $element->getLinkTarget(),
                'label' => $elementTranslation->getTextLink(),
                'url' => $url,
            ];

            if (!$element->getChildren()->isEmpty()) {
                $return[$i]['elements'] = $this->getElements($element->getChildren()->toArray());
            }
            $i++;
        }

        return $return;
    }

    /**
     * Converti une position en string
     * @param int $position
     * @return string
     */
    private function getStringPosition(int $position): string
    {
        return match ($position) {
            MenuConst::POSITION_HEADER => "HEADER",
            MenuConst::POSITION_RIGHT => "RIGHT",
            MenuConst::POSITION_FOOTER => "FOOTER",
            MenuConst::POSITION_LEFT => "LEFT",
            default => "NONE",
        };
    }

    /**
     * Retourne un menu formaté pour les API
     * @return array
     */
    public function getMenuFortApi(): array
    {
        return $this->return;
    }
}