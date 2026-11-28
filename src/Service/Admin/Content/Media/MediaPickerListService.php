<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Contenu d'un dossier de la médiathèque pour le choix d'un média dans les écrans Twig
 */

namespace App\Service\Admin\Content\Media;

use App\Entity\Admin\Content\Media\Media;
use App\Entity\Admin\Content\Media\MediaFolder;
use App\Enum\Admin\Content\Media\MediaFolderPath;
use App\Repository\Admin\Content\Media\MediaFolderRepository;
use App\Repository\Admin\Content\Media\MediaRepository;
use App\Service\Global\Media\MediaUrlBuilder;

class MediaPickerListService
{
    public function __construct(
        private readonly MediaRepository $mediaRepository,
        private readonly MediaFolderRepository $mediaFolderRepository,
        private readonly MediaUrlBuilder $mediaUrlBuilder,
    ) {}

    /**
     * Sous-dossiers puis médias hors corbeille d'un dossier, 0 pour la racine
     * @param int $folderId
     * @param string $locale langue du titre
     * @param string $orderField name ou createdAt
     * @param string $direction ASC ou DESC
     * @return array<int, array<string, mixed>>
     */
    public function list(int $folderId, string $locale, string $orderField, string $direction): array
    {
        $folder = $folderId > 0 ? $this->mediaFolderRepository->find($folderId) : null;
        if ($folder?->isTrashed()) {
            return [];
        }

        $items = array_map(
            fn(MediaFolder $child) => ['type' => 'folder', 'id' => $child->getId(), 'name' => $child->getName()],
            $this->mediaFolderRepository->findChildren($folder),
        );
        foreach ($this->mediaRepository->findInFolder($folder, $orderField, $direction) as $media) {
            $items[] = [
                'type' => 'media',
                'id' => $media->getId(),
                'name' => $media->getName(),
                'title' => $media->getMediaTranslationByLocale($locale)?->getTitle(),
                'webPath' => $this->mediaUrlBuilder->url((string) $media->getPath()),
                'thumbnail' => $this->thumbnail($media),
            ];
        }
        return $items;
    }

    /**
     * Miniature d'un média, icône de son type sans miniature
     * @param Media $media
     * @return string
     */
    private function thumbnail(Media $media): string
    {
        if ($media->hasThumbnail()) {
            return $this->mediaUrlBuilder->thumbnailUrl((string) $media->getPath());
        }
        $icon = match ($media->getExtension()) {
            'pdf' => 'file_pdf.svg',
            'xls', 'xlsx' => 'file_xls.svg',
            'csv' => 'file_csv.svg',
            'doc', 'docx' => 'file_doc.svg',
            'ppt', 'pptx' => 'file_ppt.svg',
            default => 'file.svg',
        };
        return MediaFolderPath::PATH_WEB_NATHEO_MEDIA->value . $icon;
    }
}
