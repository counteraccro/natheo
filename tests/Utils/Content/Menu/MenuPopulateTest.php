<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test Class MenuPopulate
 */

namespace App\Tests\Utils\Content\Menu;

use App\Entity\Admin\Content\Menu\Menu;
use App\Service\Admin\Content\Menu\MenuService;
use App\Tests\AppWebTestCase;
use App\Utils\Content\Menu\MenuConst;
use App\Utils\Content\Menu\MenuPopulate;

class MenuPopulateTest extends AppWebTestCase
{
    /**
     * Test méthode populate()
     * @return void
     */
    public function testPopulate(): void
    {
        $menu = $this->createMenu();

        $data = [
            'id' => $menu->getId(),
            'name' => 'Unit-test',
            'type' => MenuConst::TYPE_FOOTER_1_ROW_CENTER,
            'position' => MenuConst::POSITION_FOOTER,
            'renderOrder' => 1,
            'defaultMenu' => false,
            'disabled' => true,
            'pageMenu' => ['-1'],
            'menuElements' => [
                [
                    'id' => '1',
                    'columnPosition' => 1,
                    'rowPosition' => 1,
                    'linkTarget' => '_self',
                    'disabled' => false,
                    'parent' => '',
                    'page' => '',
                    'menuElementTranslations' => [
                        [
                            'id' => 177,
                            'locale' => 'es',
                            'textLink' => 'es- new',
                            'externalLink' => 'http://www.es.com',
                            'link' => '',
                        ],
                        [
                            'id' => 176,
                            'locale' => 'en',
                            'textLink' => 'en- new',
                            'externalLink' => 'http://www.en.com',
                            'link' => '',
                        ],
                        [
                            'id' => 175,
                            'locale' => 'fr',
                            'textLink' => 'fr- new',
                            'externalLink' => 'http://www.fr.com',
                            'link' => '',
                        ],
                    ],
                ],
            ],
        ];

        $menuService = $this->getContainer()->get(MenuService::class);
        $menuPopulate = new MenuPopulate($menu, $data, $menuService);
        $menuPopulate = $menuPopulate->populate()->getMenu();
        $this->assertInstanceOf(Menu::class, $menuPopulate);
        $this->assertEquals($data['id'], $menuPopulate->getId());
        $this->assertEquals($data['name'], $menuPopulate->getName());
        $this->assertEquals($data['type'], $menuPopulate->getType());
        $this->assertEquals($data['position'], $menuPopulate->getPosition());
        $this->assertEquals($data['renderOrder'], $menuPopulate->getRenderOrder());
        $this->assertEquals($data['defaultMenu'], $menuPopulate->isDefaultMenu());
        $this->assertEquals($data['disabled'], $menuPopulate->isDisabled());
        $this->assertTrue($menuPopulate->getPages()->isEmpty());
        $this->assertEquals(1, $menuPopulate->getMenuElements()->count());

        $menuElements = $menuPopulate->getMenuElements()->first();
        $dataMenuElements = $data['menuElements'][0];
        $this->assertEquals($dataMenuElements['columnPosition'], $menuElements->getColumnPosition());
        $this->assertEquals($dataMenuElements['rowPosition'], $menuElements->getRowPosition());
        $this->assertEquals($dataMenuElements['linkTarget'], $menuElements->getLinkTarget());
        $this->assertEquals($dataMenuElements['disabled'], $menuElements->isDisabled());
        $this->assertNull($menuElements->getParent());
        $this->assertNull($menuElements->getPage());
        $this->assertEquals(3, $menuElements->getMenuElementTranslations()->count());

        $dataTranslate = $dataMenuElements['menuElementTranslations'][2];
        $translate = $menuElements->getMenuElementTranslationByLocale('fr');
        $this->assertEquals($translate->getLocale(), $dataTranslate['locale']);
        $this->assertEquals($translate->getTextLink(), $dataTranslate['textLink']);
        $this->assertEquals($translate->getExternalLink(), $dataTranslate['externalLink']);
    }
}
