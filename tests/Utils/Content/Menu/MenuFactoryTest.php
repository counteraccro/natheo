<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 *  Test menuFactory
 */

namespace App\Tests\Utils\Content\Menu;

use App\Entity\Admin\Content\Menu\MenuElement;
use App\Tests\AppWebTestCase;
use App\Utils\Content\Menu\MenuFactory;

class MenuFactoryTest extends AppWebTestCase
{
    /**
     * Test de la méthode createMenuElement()
     * @return void
     */
    public function testCreateMenuElement(): void
    {
        $factory = new MenuFactory($this->locales);

        $menuElement = $factory->createMenuElement();
        $this->assertInstanceOf(MenuElement::class, $menuElement);
        $this->assertEquals(count($this->locales), $menuElement->getMenuElementTranslations()->count());
    }
}
