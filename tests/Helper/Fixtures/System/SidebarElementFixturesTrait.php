<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Fixture création sidebarElement
 */
namespace App\Tests\Helper\Fixtures\System;

use App\Entity\Admin\System\SidebarElement;
use App\Tests\Helper\FakerTrait;

trait SidebarElementFixturesTrait
{
    use FakerTrait;

    /**
     * Création d'un sidebarElement
     * @param array $customData
     * @param bool $persist
     * @return SidebarElement
     */
    public function createSidebarElement(array $customData = [], bool $persist = true) :SidebarElement
    {

        $data = [
            'icon' => self::getFaker()->text(20),
            'label' => self::getFaker()->text(),
            'route' => self::getFaker()->text(),
            'disabled' => self::getFaker()->boolean(),
            'role' => 'ROLE_USER',
            'description' => self::getFaker()->text(),
            'lock' => self::getFaker()->boolean(),
        ];

        /** @var SidebarElement $sidebar */
        $sidebar = $this->initEntity(SidebarElement::class, array_merge($data, $customData));
        if(isset($customData['children'])){
           $sidebar->addChild($this->createSidebarElement($customData['children']));
        }

        if ($persist) {
            $this->persistAndFlush($sidebar);
        }
        return $sidebar;
    }
}