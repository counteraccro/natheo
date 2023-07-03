<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Fixture pour l'entité SidebarElement
 */

namespace App\DataFixtures\Admin\System;

use App\DataFixtures\AppFixtures;
use App\Entity\Admin\System\SidebarElement;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

class SidebarElementFixtures extends AppFixtures implements FixtureGroupInterface, OrderedFixtureInterface
{
    const SIDEBAR_ELEMENT_FIXTURES_DATA_FILE = 'sidebar_element_fixtures_data.yaml';

    public function load(ObjectManager $manager): void
    {
        $data = Yaml::parseFile($this->pathDataFixtures . self::SIDEBAR_ELEMENT_FIXTURES_DATA_FILE);
        foreach ($data['sidebarElement'] as $id => $data) {
            $sidebarElement = new SidebarElement();
            foreach ($data as $key => $value) {
                if ($key === 'children') {
                    $this->addReference($id, $sidebarElement);
                    foreach ($value as $tab) {

                        $tmpKey = key($tab);
                        $sidebarElementChild = new SidebarElement();
                        foreach ($tab[$tmpKey] as $keyChild => $valueChild) {
                            if ($keyChild === 'parent') {
                                $sidebarElementChild->setParent($this->getReference($id));
                                $manager->persist($sidebarElementChild);
                            } else {
                                $this->setData($keyChild, $valueChild, $sidebarElementChild);
                            }
                        }
                    }
                } else {
                    $this->setData($key, $value, $sidebarElement);
                }
            }
            $manager->persist($sidebarElement);
        }
        $manager->flush();
    }

    /**
     * Set une valeur pour l'objet SitebarElement
     * @param string $key
     * @param mixed $value
     * @param SidebarElement $sidebarElement
     * @return void
     */
    private function setData(string $key, mixed $value, SidebarElement $sidebarElement): void
    {
        $func = 'set' . ucfirst($key);
        $sidebarElement->$func($value);
    }

    public static function getGroups(): array
    {
        return [self::GROUP_DEVTOOLS, self::GROUP_SIDEBAR_ELEMENT];
    }

    /**
     * Définition de l'ordre
     * @return int
     */
    public function getOrder(): int
    {
        return 2; // smaller means sooner
    }
}