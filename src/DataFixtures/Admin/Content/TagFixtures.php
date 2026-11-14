<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Fixtures pour la génération des tags
 */

namespace App\DataFixtures\Admin\Content;

use App\DataFixtures\AppFixtures;
use App\Entity\Admin\Content\Tag\Tag;
use App\Entity\Admin\Content\Tag\TagTranslation;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

class TagFixtures extends AppFixtures implements FixtureGroupInterface, OrderedFixtureInterface
{
    const TAG_FIXTURES_DATA_FILE = 'content' . DIRECTORY_SEPARATOR . 'tag_fixtures_data.yaml';

    /**
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $data = Yaml::parseFile($this->pathDataFixtures . self::TAG_FIXTURES_DATA_FILE);
        foreach ($data['tag'] as $ref => $dataTag) {
            $tag = new Tag();
            $tag->setUser($this->getUserReference($dataTag['user'] ?? 'User'));
            $tag->setColor($dataTag['color']);
            foreach ($dataTag['label'] as $locale => $label) {
                $tag->addTagTranslation(
                    $this->populateEntity(['locale' => $locale, 'label' => $label], new TagTranslation()),
                );
            }
            $manager->persist($tag);
            $this->addReference($ref, $tag);
        }
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return [self::GROUP_TAG, self::GROUP_CONTENT];
    }

    public function getOrder(): int
    {
        return 201;
    }
}
