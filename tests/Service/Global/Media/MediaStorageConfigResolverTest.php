<?php

declare(strict_types=1);
/**
 * Test de MediaStorageConfigResolver
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Tests\Service\Global\Media;

use App\Enum\Admin\System\Options\OptionSystem;
use App\Service\Admin\System\OptionSystemService;
use App\Service\Global\Media\MediaStorageConfigResolver;
use App\Tests\AppWebTestCase;

class MediaStorageConfigResolverTest extends AppWebTestCase
{
    /**
     * Les dossiers de test sont utilisés quel que soit OS_MEDIA_PATH, OS_MEDIA_URL préfixe les URL
     * @return void
     */
    public function testResolveInTestEnvironment(): void
    {
        $optionSystemService = $this->container->get(OptionSystemService::class);
        $optionSystemService->saveValueByKee(OptionSystem::OS_MEDIA_PATH->value, 'medias');
        $optionSystemService->saveValueByKee(OptionSystem::OS_MEDIA_URL->value, 'https://site.fr/');
        $projectDir = $this->container->getParameter('kernel.project_dir');

        $config = (new MediaStorageConfigResolver($optionSystemService, $projectDir, 'test'))->resolve();

        $this->assertSame($projectDir . '/public/assets/natheotheque-test', $config->storageRoot);
        $this->assertSame($projectDir . '/var/media-trash-test', $config->trashRoot);
        $this->assertSame('https://site.fr/assets/natheotheque-test/2026/11/a.png', $config->url('2026/11/a.png'));
    }

    /**
     * Hors test, OS_MEDIA_PATH donne le dossier, sans remonter au-dessus du dossier public
     * @return void
     */
    public function testResolveFolderFromOption(): void
    {
        $optionSystemService = $this->container->get(OptionSystemService::class);
        $optionSystemService->saveValueByKee(OptionSystem::OS_MEDIA_PATH->value, '../../medias/');
        $optionSystemService->saveValueByKee(OptionSystem::OS_MEDIA_URL->value, '');

        $config = (new MediaStorageConfigResolver($optionSystemService, '/projet', 'prod'))->resolve();

        $this->assertSame('/projet/public/assets/medias', $config->storageRoot);
        $this->assertSame('/projet/var/media-trash', $config->trashRoot);
        $this->assertSame('/assets/medias/2026/11/a.png', $config->url('2026/11/a.png'));
    }
}
