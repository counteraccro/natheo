<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Fixture pour l'entité SidebarElement
 */

namespace App\DataFixtures\Admin;

use App\DataFixtures\AppFixtures;
use App\Entity\Admin\SidebarElement;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

class SidebarElementFixtures extends AppFixtures implements FixtureGroupInterface
{
    const SIDEBAR_ELEMENT_FIXTURES_DATA_FILE = 'sidebarElementFixturesData.yaml';

    public function load(ObjectManager $manager): void
    {
        $data = [];
        $data = Yaml::parseFile($this->pathDataFixtures . self::SIDEBAR_ELEMENT_FIXTURES_DATA_FILE);

        foreach ($data['sidebarElement'] as $id => $data) {
            $sidebarElement = new SidebarElement();
            foreach ($data as $key => $value) {
                if ($key === 'children') {
                    $this->addReference($id, $sidebarElement);
                    foreach ($value as $tab) {

                        $tmpKey = key($tab);
                        $sidebarElementChild = new SidebarElement();
                        foreach($tab[$tmpKey] as $keyChild => $valueChild) {
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
     * @return SidebarElement
     */
    private function setData(string $key, mixed $value, SidebarElement $sidebarElement): SidebarElement
    {
        $func = 'set' . ucfirst($key);
        $sidebarElement->$func($value);
        return $sidebarElement;
    }

    public static function getGroups(): array
    {
        return ['devTools', 'sidebarElement'];
    }
}
