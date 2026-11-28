<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Fixtures pour la génération des dossiers de media
 */

namespace App\DataFixtures\Admin\Content\Media;

use App\DataFixtures\AppFixtures;
use App\Entity\Admin\Content\Media\MediaFolder;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

class MediaFolderFixtures extends AppFixtures implements FixtureGroupInterface, OrderedFixtureInterface
{
    const MEDIA_FOLDER_FIXTURES_DATA_FILE =
        'content' . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'media_folder_fixtures_data.yaml';

    /**
     * Dossiers créés dans l'ordre du fichier, un parent toujours avant ses enfants
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $data = Yaml::parseFile($this->pathDataFixtures . self::MEDIA_FOLDER_FIXTURES_DATA_FILE);
        foreach ($data['media_folder'] as $ref => $folder) {
            $mediaFolder = new MediaFolder();
            $mediaFolder->setName($folder['name']);
            $mediaFolder->setUser($this->getUserReference($folder['user'] ?? 'User'));
            if (!empty($folder['parent'])) {
                $mediaFolder->setParent($this->getReference($folder['parent'], MediaFolder::class));
            }
            $manager->persist($mediaFolder);
            $manager->flush();
            $mediaFolder->setTreePath($mediaFolder->buildTreePath());
            $this->addReference($ref, $mediaFolder);
        }
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return [self::GROUP_MEDIA, self::GROUP_CONTENT];
    }

    public function getOrder(): int
    {
        return 202;
    }
}
