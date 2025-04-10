<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test MenuConvertToArray
 */

namespace App\Tests\Utils\Content\Menu;

use App\Tests\AppWebTestCase;
use App\Utils\Content\Menu\MenuConvertToArray;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class MenuToConvertToArrayTest extends AppWebTestCase
{
    /**
     * Test de la méthode convertToArray()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testConvertToArray() :void
    {
        /** @var MenuConvertToArray $menuConvertToArray */
        $menuConvertToArray = $this->container->get(MenuConvertToArray::class);

        $menu = $this->createMenu();
        $menuElement = $this->createMenuElement($menu);
        foreach($this->locales as $locale) {
            $this->createMenuElementTranslation($menuElement, ['locale' => $locale]);
        }
        $subMenuElement = $this->createMenuElement($menu, $menuElement);
        foreach($this->locales as $locale) {
            $this->createMenuElementTranslation($subMenuElement, ['locale' => $locale]);
        }

        $menuArray = $menuConvertToArray->convertToArray($menu->getId());

        $this->assertIsArray($menuArray);
        $this->assertArrayHasKey('id', $menuArray);
        $this->assertEquals($menu->getId(), $menuArray['id']);
        $this->assertArrayHasKey('name', $menuArray);
        $this->assertEquals($menu->getName(), $menuArray['name']);
        $this->assertArrayHasKey('type', $menuArray);
        $this->assertEquals($menu->getType(), $menuArray['type']);
        $this->assertArrayHasKey('position', $menuArray);
        $this->assertEquals($menu->getPosition(), $menuArray['position']);
        $this->assertArrayHasKey('renderOrder', $menuArray);
        $this->assertEquals($menu->getRenderOrder(), $menuArray['renderOrder']);
        $this->assertArrayHasKey('defaultMenu', $menuArray);
        $this->assertEquals($menu->isDefaultMenu(), $menuArray['defaultMenu']);
        $this->assertArrayHasKey('disabled', $menuArray);
        $this->assertEquals($menu->isDisabled(), $menuArray['disabled']);
        $this->assertCount(1, $menuArray['menuElements']);

        $menuElementArray = $menuArray['menuElements'][0];
        $menuElement = $menu->getMenuElements()->first();
        $this->assertArrayHasKey('id', $menuElementArray);
        $this->assertEquals($menuElement->getId(), $menuElementArray['id']);
        $this->assertArrayHasKey('columnPosition', $menuElementArray);
        $this->assertEquals($menuElement->getColumnPosition(), $menuElementArray['columnPosition']);
        $this->assertArrayHasKey('rowPosition', $menuElementArray);
        $this->assertEquals($menuElement->getRowPosition(), $menuElementArray['rowPosition']);
        $this->assertArrayHasKey('linkTarget', $menuElementArray);
        $this->assertEquals($menuElement->getLinkTarget(), $menuElementArray['linkTarget']);

        $this->assertCount(3, $menuElementArray['menuElementTranslations']);
        $menuElementTranslationArray = $menuElementArray['menuElementTranslations'][0];
        $menuElementTranslation = $menuElement->getMenuElementTranslations()->first();

        $this->assertArrayHasKey('id', $menuElementTranslationArray);
        $this->assertEquals($menuElementTranslation->getId(), $menuElementTranslationArray['id']);
        $this->assertArrayHasKey('locale', $menuElementTranslationArray);
        $this->assertEquals($menuElementTranslation->getLocale(), $menuElementTranslationArray['locale']);
        $this->assertArrayHasKey('textLink', $menuElementTranslationArray);
        $this->assertEquals($menuElementTranslation->getTextLink(), $menuElementTranslationArray['textLink']);
        $this->assertArrayHasKey('externalLink', $menuElementTranslationArray);
        $this->assertEquals($menuElementTranslation->getExternalLink(), $menuElementTranslationArray['externalLink']);
        $this->assertArrayHasKey('link', $menuElementTranslationArray);
        $this->assertEquals('', $menuElementTranslationArray['link']);

        $this->assertCount(1, $menuElementArray['children']);
        $childrenArray = $menuElementArray['children'][0];
        $children = $menuElement->getChildren()->first();

        $this->assertArrayHasKey('id', $childrenArray);
        $this->assertEquals($children->getId(), $childrenArray['id']);
        $this->assertCount(3, $childrenArray['menuElementTranslations']);
    }
}