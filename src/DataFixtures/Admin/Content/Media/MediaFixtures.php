<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Fixture pour l'entité Media
 */

namespace App\DataFixtures\Admin\Content\Media;

use App\DataFixtures\AppFixtures;
use App\Entity\Admin\Content\Media\Media;
use App\Entity\Admin\Content\Media\MediaFolder;
use App\Entity\Admin\Content\Media\MediaTranslation;
use App\Populate\Admin\Content\Media\MediaPopulate;
use App\Service\Global\Media\MediaStorage;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use League\Flysystem\FilesystemException;
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

    /**
     * Dossier des fichiers source des fixtures, relatif au projet
     */
    const SOURCE_FOLDER = 'public' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'fixtures';

    private MediaStorage $mediaStorage;

    /**
     * @param ContainerInterface $handlers
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        #[
            AutowireLocator([
                'container' => ContainerBagInterface::class,
                'mediaStorage' => MediaStorage::class,
            ]),
        ]
        private readonly ContainerInterface $handlers,
    ) {
        $this->mediaStorage = $this->handlers->get('mediaStorage');
        parent::__construct($this->handlers);
    }

    /**
     * Vide le stockage puis y range chaque fichier source
     * @param ObjectManager $manager
     * @return void
     * @throws FilesystemException
     */
    public function load(ObjectManager $manager): void
    {
        $this->mediaStorage->clear();
        $sourceFolder = $this->container->get('kernel.project_dir') . DIRECTORY_SEPARATOR . self::SOURCE_FOLDER;

        $data = Yaml::parseFile($this->pathDataFixtures . self::MEDIA_FIXTURES_DATA_FILE);
        foreach ($data['media'] as $ref => $dataMedia) {
            $stored = $this->mediaStorage->store(
                $sourceFolder . DIRECTORY_SEPARATOR . $dataMedia['file'],
                $dataMedia['file'],
            );
            $media = MediaPopulate::fromStoredFile(new Media(), $stored, $dataMedia['file']);
            $media->setUser($this->getUserReference($dataMedia['user'] ?? 'User'));
            if (!empty($dataMedia['folder'])) {
                $media->setMediaFolder($this->getReference($dataMedia['folder'], MediaFolder::class));
            }
            foreach ($dataMedia['title'] as $locale => $title) {
                $media->addMediaTranslation(
                    (new MediaTranslation())
                        ->setLocale($locale)
                        ->setTitle($title)
                        ->setAlt($dataMedia['alt'][$locale] ?? null),
                );
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
