<?php
/**
 * Permet de convertir une entité Menu en array adapté pour vueJS
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Content\Menu;

use App\Entity\Admin\Content\Menu\Menu;
use App\Entity\Admin\Content\Menu\MenuElement;
use App\Service\Admin\Content\Menu\MenuService;
use App\Service\Admin\Tools\SqlManagerService;
use App\Utils\Global\DataBase;
use Doctrine\Common\Collections\Collection;

class MenuConvertToArray
{
    /**
     * @param MenuService $menuService
     */
    public function __construct(
        private readonly MenuService $menuService,
        private readonly DataBase    $dataBase
    )
    {
    }

    /**
     * Convertie un menu trouvé par son id en array adapté aux scripts côté vue
     * @param int|null $id
     * @return array
     */
    public function convertToArray(int $id = null): array
    {
        $return = $this->createStructure(Menu::class, ['createdAt', 'updateAt', 'userId']);

        $menu = null;
        if ($id !== null) {
            $menu = $this->menuService->findOneById(Menu::class, $id);
            $return = $this->mergeData($return, $menu);
        }


        return $return;
    }

    /**
     * Merge les données du menu dans la structure
     * @param array $structure
     * @param Menu $menu
     * @return array
     */
    private function mergeData(array $structure, Menu $menu): array
    {
        $structure = $this->generiqueMerge($structure, $menu);

        if (!$menu->getMenuElements()->isEmpty()) {
            $structure = $this->mergeMenuElements($structure, $menu->getMenuElements());
        }

        return $structure;
    }

    /**
     * Merge les données du menuElement
     * @param array $structure
     * @param Collection $menuElements
     * @return array
     */
    private function mergeMenuElements(array $structure, Collection $menuElements): array
    {
        $structureMenuElement = $this->createStructure(MenuElement::class);
        foreach ($menuElements as $menuElement) {
            $structure['menuElements'][] = $this->generiqueMerge($structureMenuElement, $menuElement);
        }
        return $structure;
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
        return $structure;
    }
}
