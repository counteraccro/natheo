<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Fixtures pour la génération des tags
 */

namespace App\DataFixtures\Admin\Content;

use App\DataFixtures\AppFixtures;
use App\Entity\Admin\Content\Tag;
use App\Entity\Admin\Content\TagTranslation;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

class TagFixtures extends AppFixtures implements FixtureGroupInterface, OrderedFixtureInterface
{

    const TAG_FIXTURES_DATA_FILE = 'tag_fixtures_data.yaml';

    /**
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $data = Yaml::parseFile($this->pathDataFixtures . self::TAG_FIXTURES_DATA_FILE);
        foreach ($data['tag'] as $id => $data) {
            $tag = new Tag();
            foreach ($data as $key => $value) {
                if ($key === 'translate') {
                    foreach ($value as $translate) {
                        $tagTranslate = new TagTranslation();
                        foreach ($translate as $keyChild => $valueChild) {
                            $this->setData($keyChild, $valueChild, $tagTranslate);
                        }
                        $tagTranslate->setTag($tag);
                        $manager->persist($tagTranslate);
                    }
                } else {
                    $this->setData($key, $value, $tag);
                }
            }
            $manager->persist($tag);
        }
        $manager->flush();
    }

    /**
     * Set une valeur pour l'objet Tag et TagTranslate
     * @param string $key
     * @param mixed $value
     * @param mixed $entity
     * @return void
     */
    private function setData(string $key, mixed $value, mixed $entity): void
    {
        $func = 'set' . ucfirst($key);
        $entity->$func($value);
    }

    public static function getGroups(): array
    {
        return [self::GROUP_TAG, self::GROUP_CONTENT];
    }

    public function getOrder(): int
    {
        return 5;
    }
}
