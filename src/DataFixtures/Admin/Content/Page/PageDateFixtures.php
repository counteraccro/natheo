<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Fixtures pour dater les pages (clé « date » de chaque page), une fois toutes leurs modifications faites
 */
namespace App\DataFixtures\Admin\Content\Page;

use App\DataFixtures\AppFixtures;
use App\Entity\Admin\Content\Page\Page;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PageDateFixtures extends AppFixtures implements FixtureGroupInterface, OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $dates = [];
        foreach ($this->loadYamlFolder($this->pathDataFixtures . PageFixtures::PAGE_FIXTURES_FOLDER) as $ref => $file) {
            if (isset($file['data']['date'])) {
                $dates[] = [$this->getReference($ref, Page::class), $file['data']['date']];
            }
        }
        $this->backdate($manager, $dates);
    }

    /**
     * @return array|string[]
     */
    public static function getGroups(): array
    {
        return [self::GROUP_PAGE, self::GROUP_CONTENT];
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return 300;
    }
}
