<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Fixture pour l'entité Media
 */

namespace App\DataFixtures\Admin\Content\Media;

use App\DataFixtures\AppFixtures;
use App\Entity\Admin\Content\Media\Media;
use App\Entity\Admin\Content\Media\MediaFolder;
use App\Entity\Admin\System\User;
use App\Service\Admin\Content\Media\MediaService;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Yaml\Yaml;

class MediaFixtures extends AppFixtures implements FixtureGroupInterface, OrderedFixtureInterface
{
    const MEDIA_FIXTURES_DATA_FILE =
        'content' . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'media_fixtures_data.yaml';

    private MediaService $mediaService;

    /**
     * @param ContainerInterface $handlers
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        #[
            AutowireLocator([
                'container' => ContainerBagInterface::class,
                'mediaService' => MediaService::class,
            ]),
        ]
        private readonly ContainerInterface $handlers,
    ) {
        $this->mediaService = $this->handlers->get('mediaService');
        parent::__construct($this->handlers);
    }

    /**
     * @param ObjectManager $manager
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function load(ObjectManager $manager): void
    {
        $data = Yaml::parseFile($this->pathDataFixtures . self::MEDIA_FIXTURES_DATA_FILE);

        foreach ($data['media'] as $ref => $data) {
            $media = new Media();
            foreach ($data as $key => $value) {
                switch ($key) {
                    case 'folder':
                        if (!empty($value)) {
                            $media->setMediaFolder($this->getReference($value, MediaFolder::class));
                        }
                        break;
                    case 'user':
                        $media->setUser($this->getReference($value, User::class));
                        break;
                    case 'description':
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
            $this->addReference($ref, $media);
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
