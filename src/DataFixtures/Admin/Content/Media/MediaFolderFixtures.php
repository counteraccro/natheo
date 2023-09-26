<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Fixtures pour la génération des dossiers de media
 */

namespace App\DataFixtures\Admin\Content\Media;

use App\DataFixtures\AppFixtures;
use App\Entity\Admin\Content\Media\MediaFolder;
use App\Service\Admin\Content\Media\MediaFolderService;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Yaml\Yaml;
use function PHPUnit\Framework\isEmpty;

class MediaFolderFixtures extends AppFixtures implements FixtureGroupInterface, OrderedFixtureInterface
{
    private MediaFolderService $mediaFolderService;


    public function __construct(
        ContainerBagInterface $params,
        MediaFolderService    $mediaFolderService,
    )
    {
        $this->mediaFolderService = $mediaFolderService;
        parent::__construct($params);
    }

    const TAG_FIXTURES_DATA_FILE = 'media_folder_fixtures_data.yaml';

    public function load(ObjectManager $manager): void
    {
        $this->mediaFolderService->resetAllMedia();

        $data = Yaml::parseFile($this->pathDataFixtures . self::TAG_FIXTURES_DATA_FILE);
        foreach ($data['media_folder'] as $ref => $folder) {
            $mediaFolder = new MediaFolder();

            foreach ($folder as $key => $value) {
                switch ($key) {
                    case 'parent' :
                        $this->setParent($value, $mediaFolder);
                        break;
                    default:
                        $this->setData($key, $value, $mediaFolder);
                }
            }
            $manager->persist($mediaFolder);
            $this->mediaFolderService->createFolder($mediaFolder);
            $this->addReference($ref, $mediaFolder);
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

    /**
     * Enregistre un parent
     * @param mixed $ref
     * @param MediaFolder $mediaFolder
     * @return void
     */
    private function setParent(mixed $ref, MediaFolder $mediaFolder): void
    {
        if (!isEmpty($ref) || $ref != null) {
            $mediaFolder->setParent($this->getReference($ref));
        }
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
