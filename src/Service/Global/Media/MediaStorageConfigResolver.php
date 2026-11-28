<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Emplacements du stockage des médias lus dans les options système
 */

namespace App\Service\Global\Media;

use App\Dto\Media\MediaStorageConfig;
use App\Enum\Admin\Content\Media\MediaFolderPath;
use App\Enum\Admin\System\Options\OptionSystem;
use App\Service\Admin\System\OptionSystemService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class MediaStorageConfigResolver
{
    private ?MediaStorageConfig $config = null;

    public function __construct(
        private readonly OptionSystemService $optionSystemService,
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
        #[Autowire('%kernel.environment%')] private readonly string $environment,
    ) {}

    /**
     * Emplacements du stockage, lus une fois par requête ; l'environnement de test a ses propres dossiers
     * @return MediaStorageConfig
     */
    public function resolve(): MediaStorageConfig
    {
        if ($this->config !== null) {
            return $this->config;
        }

        $folder = implode(
            '/',
            MediaStorageConfig::segments(
                (string) $this->optionSystemService->getValueByKey(OptionSystem::OS_MEDIA_PATH->value),
            ),
        );
        if ($folder === '') {
            $folder = MediaFolderPath::NAME_DEFAULT_FOLDER_MEDIATHEQUE->value;
        }
        $trash = MediaFolderPath::STORAGE_TRASH_ROOT->value;
        if ($this->environment === 'test') {
            $folder = MediaFolderPath::NAME_DEFAULT_FOLDER_MEDIATHEQUE_TEST->value;
            $trash .= '-test';
        }

        return $this->config = new MediaStorageConfig(
            $this->projectDir . '/' . MediaFolderPath::STORAGE_PUBLIC_ROOT->value . '/' . $folder,
            $this->projectDir . '/' . $trash,
            MediaFolderPath::STORAGE_WEB_ROOT->value . '/' . $folder,
            (string) $this->optionSystemService->getValueByKey(OptionSystem::OS_MEDIA_URL->value),
        );
    }
}
