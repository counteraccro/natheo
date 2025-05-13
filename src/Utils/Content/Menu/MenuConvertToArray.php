<?php
/**
 * Permet de convertir une entité Menu en array adapté pour vueJS
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Content\Menu;

use App\Entity\Admin\Content\Menu\Menu;
use App\Entity\Admin\Content\Menu\MenuElement;
use App\Entity\Admin\Content\Menu\MenuElementTranslation;
use App\Entity\Admin\Content\Page\Page;
use App\Service\Admin\Content\Menu\MenuService;
use App\Service\Admin\Content\Page\PageService;
use App\Utils\Global\Database\DataBase;
use Doctrine\Common\Collections\Collection;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MenuConvertToArray
{
    /**
     * @param MenuService $menuService
     * @param DataBase $dataBase
     * @param PageService $pageService
     * @param TranslatorInterface $translator
     */
    public function __construct(
        private readonly MenuService         $menuService,
        private readonly DataBase            $dataBase,
        private readonly PageService         $pageService,
        private readonly TranslatorInterface $translator
    )
    {
    }

    /**
     * Convertie un menu trouvé par son id en array adapté aux scripts côté vue
     * @param int|null $id
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function convertToArray(?int $id = null): array
    {
        $return = $this->createStructure(Menu::class, ['createdAt', 'updateAt', 'userId']);
        $return['position'] = MenuConst::POSITION_HEADER;
        $return['type'] = MenuConst::TYPE_HEADER_SIDE_BAR;
        $return['defaultMenu'] = false;
        $return['renderOrder'] = 1;
        $return['disabled'] = true;
        $return['name'] = '';
        if ($id !== null) {
            $menu = $this->menuService->findOneById(Menu::class, $id);
            $return = $this->mergeData($return, $menu);
        } else {
            $return['menuElements'] = [];
        }
        return $return;
    }

    /**
     * Merge les données du menu dans la structure
     * @param array $structure
     * @param Menu $menu
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function mergeData(array $structure, Menu $menu): array
    {
        $structure = $this->generiqueMerge($structure, $menu);
        $structure['menuElements'] = [];

        $menuElement = $this->menuService->getMenuElementByMenuAndParent($menu->getId());
        if (!empty($menuElement)) {
            $structure['menuElements'] = $this->mergeMenuElements($menuElement);
        }

        $structure['menuElements'] = array_values($structure['menuElements']);
        return $structure;
    }

    /**
     * Merge les données du menuElement
     * @param array $menuElements
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function mergeMenuElements(array $menuElements): array
    {
        $structureMenuElement = $this->createStructure(MenuElement::class);
        $structure = [];

        $key = 0;
        foreach ($menuElements as $menuElement) {
            /** @var MenuElement $menuElement */

            $structure[$key] = $this->generiqueMerge($structureMenuElement, $menuElement);
            $structure[$key]['page'] = '';
            if ($menuElement->getPage() !== null) {
                $structure[$key]['page'] = $menuElement->getPage()->getId();
            }

            if (!$menuElement->getMenuElementTranslations()->isEmpty()) {
                $structure[$key]['menuElementTranslations'] =
                    $this->mergeMenuElementTranslation($menuElement->getMenuElementTranslations(), $menuElement->getPage());
            }

            if ($menuElement->getParent() !== null) {
                $structure[$key]['parent'] = $menuElement->getParent()->getId();
            }

            if (!$menuElement->getChildren()->isEmpty()) {
                $structure[$key]['children'] = $this->mergeMenuElements($menuElement->getChildren()->toArray());
            }
            $key++;

        }
        return $structure;
    }

    /**
     * Merge les données du menuElementTranslation
     * @param Collection $menuElementTranslations
     * @param Page|null $page
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function mergeMenuElementTranslation(Collection $menuElementTranslations, ?Page $page = null): array
    {
        $return = [];
        $structureMenuElementTranslation = $this->createStructure(MenuElementTranslation::class);
        $i = 0;
        foreach ($menuElementTranslations as $menuElementTranslation) {

            /** @var MenuElementTranslation $menuElementTranslation */

            $return[$i] = $this->generiqueMerge($structureMenuElementTranslation, $menuElementTranslation);
            $return[$i]['link'] = '';

            if ($page !== null) {
                $category = $this->pageService->getCategoryById($page->getCategory());
                $pageTranslate = $page->getPageTranslationByLocale($menuElementTranslation->getLocale());
                $return[$i]['link'] = strtolower($category) . '/' . $pageTranslate->getUrl();
            }
            $i++;
        }
        return $return;
    }

    /**
     * Créer la structure du format de retour du tableau
     * @param string $class
     * @param array $exclude
     * @return array
     */
    private function createStructure(string $class, array $exclude = []): array
    {
        $array = [];
        $structureMenu = $this->dataBase->getNameAndColumByEntity($class);

        foreach ($structureMenu['column'] as $column) {
            if (!in_array($column, $exclude)) {
                $array[$column] = '';
            }
        }
        return $array;
    }

    /**
     * Merge de façon générique les données de $object dans $structure
     * @param array $structure
     * @param $object
     * @return array
     */
    private function generiqueMerge(array $structure, $object): array
    {
        foreach ($structure as $key => $item) {
            if (method_exists($object, 'get' . ucfirst($key))) {
                $structure[$key] = $object->{'get' . ucfirst($key)}();
            }

            if (method_exists($object, 'is' . ucfirst($key))) {
                $structure[$key] = $object->{'is' . ucfirst($key)}();
            }
        }

        if ($object instanceof MenuElement) {
            $structure['parent'] = "";
        }

        if ($object instanceof Menu) {
            $structure['pageMenu'] = [];
            if (!$object->getPages()->isEmpty()) {
                foreach ($object->getPages() as $page) {
                    $structure['pageMenu'][] = $page->getId();
                }
            } else {
                $structure['pageMenu'] = ['-1'];
            }

        }
        return $structure;
    }
}
