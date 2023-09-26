<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Fixture pour l'entité Media
 */

namespace App\DataFixtures\Admin\Content;

use App\DataFixtures\AppFixtures;
use App\Entity\Admin\Content\Media\Media;
use App\Service\Admin\Content\Media\MediaService;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Yaml\Yaml;

class MediaFixtures extends AppFixtures implements FixtureGroupInterface, OrderedFixtureInterface
{
    const MEDIA_FIXTURES_DATA_FILE = 'media_fixtures_data.yaml';

    private MediaService $mediaService;

    /**
     * @param ContainerBagInterface $params
     * @param MediaService $mediaService
     */
    public function __construct(ContainerBagInterface $params, MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
        parent::__construct($params);
    }

    public function load(ObjectManager $manager): void
    {
        $data = Yaml::parseFile($this->pathDataFixtures . self::MEDIA_FIXTURES_DATA_FILE);

        foreach ($data['media'] as $data) {
            $media = new Media();
            foreach ($data as $key => $value) {
                switch ($key) {
                    case 'folder' :
                        if(!empty($value))
                        {
                            $media->setMediaFolder($this->getReference($value));
                        }
                        break;
                    case 'user' :
                        $media->setUser($this->getReference($value));
                        break;
                    case 'description' :
                        $media->setDescription($value);
                        break;
                    case 'image':
                        $this->mediaService->moveMediaFixture($value, $media);
                        $this->mediaService->UpdateMediaFile($media, $value);
                        break;
                    default:

                }
            }
            $manager->persist($media);
        }
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return [self::GROUP_MEDIA, self::GROUP_CONTENT];
    }

    public function getOrder(): int
    {
        return 203;
    }
}
