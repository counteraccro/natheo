<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service qui gère les médias
 */

namespace App\Service\Admin\Content\Media;

use App\Entity\Admin\Content\Media;
use App\Entity\Admin\Content\MediaFolder;
use App\Repository\Admin\Content\MediaRepository;
use App\Utils\Content\Media\MediaFolderConst;
use App\Utils\Utils;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class MediaService extends MediaFolderService
{

    /**
     * Pour les fixtures, déplace un média du dossier fixture vers le dossier média
     * @param string $file
     * @param Media $media
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function moveMediaFixture(string $file, Media $media): void
    {
        $rootPath = $this->containerBag->get('kernel.project_dir');
        $fixturesPath = $rootPath . DIRECTORY_SEPARATOR .
            MediaFolderConst::ROOT_FOLDER_NAME . 'fixtures' . DIRECTORY_SEPARATOR;

        $fileSystem = new Filesystem();

        $urlOrigin = $fixturesPath . $file;
        $urlCopy = $this->rootPathMedia . DIRECTORY_SEPARATOR . $file;
        if ($this->canCreatePhysicalFolder && $media->getMediaFolder() != null) {
            $urlCopy = $this->getPathFolder($media->getMediaFolder()) . $file;
        }
        $fileSystem->copy($urlOrigin, $urlCopy);
    }

    /**
     * Met à jour un média en fonction du fichier
     * @param Media $media
     * @param string $file
     * @return void
     */
    public function UpdateMediaFile(Media $media, string $file): void
    {
        $urlFile = $this->rootPathMedia;
        if ($this->canCreatePhysicalFolder && $media->getMediaFolder() != null) {
            $urlFile = $this->getPathFolder($media->getMediaFolder());
        }
        $finder = new Finder();
        $finder->files()->in($urlFile)->name($file);

        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $media->setExtension($file->getExtension());
                $media->setPath($file->getPath());
                $media->setTitle($file->getFilenameWithoutExtension());
                $media->setName($file->getFilename());
                $media->setWebPath($this->getWebPath($media));
                $media->setSize($file->getSize());
                $media->setType('Image');
            }
        }
    }

    /**
     * Génère le web path d'un média
     * @param Media $media
     * @return string
     */
    public function getWebPath(Media $media): string
    {
        $mediaFolder = $media->getMediaFolder();
        $path = $this->webPathMedia . '/' . $media->getName();
        if ($mediaFolder != null) {
            $path = $this->webPathMedia . $mediaFolder->getPath() . '/' .
                $mediaFolder->getName() . '/' . $media->getName();
        }
        return $path;
    }

    /**
     * Retourne l'ensemble des médias et dossier lié à un dossier
     * @return array
     */
    public function getALlMediaAndMediaFolderByMediaFolder(MediaFolder $mediaFolder = null)
    {
        $medias = $this->getMediaByMediaFolder($mediaFolder);
        $folders = $this->getMediaFolderByMediaFolder($mediaFolder);

        $return = [];

        /** @var Media $media */
        foreach($medias as $media)
        {
            $return[] = [
                'type' => 'media',
                'id' => $media->getId(),
                'name' => $media->getName(),
                'description' => $media->getDescription(),
                'size' => Utils::getSizeName($media->getSize()),
                'webPath' => $media->getWebPath(),
            ];
        }

        /** @var MediaFolder $folder */
        foreach($folders as $folder)
        {
            $return[] = [
                'type' => 'folder',
                'id' => $folder->getId(),
                'name' => $folder->getName()
            ];
        }

        return $return;
    }

    /**
     * Retourne une liste de média en fonction d'un mediaFolder
     * @param MediaFolder|null $mediaFolder
     * @return float|int|mixed|string
     */
    public function getMediaByMediaFolder(MediaFolder $mediaFolder = null): mixed
    {
        /** @var MediaRepository $repo */
        $repo = $this->getRepository(Media::class);
        return $repo->findByMediaFolder($mediaFolder);
    }

    /**
     * Retourne la traduction pour la médiathèque
     * @return array
     */
    public function getMediatequeTranslation(): array
    {
        return [
            'loading' => $this->translator->trans('media.mediatheque.loading', domain: 'media'),
            'btn_new_folder' => $this->translator->trans('media.mediatheque.btn.new.folder', domain: 'media'),
            'btn_new_media' => $this->translator->trans('media.mediatheque.btn.new.media', domain: 'media'),
            'btn_filtre' => $this->translator->trans('media.mediatheque.btn.filtre', domain: 'media'),
            'filtre_date' => $this->translator->trans('media.mediatheque.filtre.date', domain: 'media'),
            'filtre_nom' => $this->translator->trans('media.mediatheque.filtre.nom', domain: 'media'),
            'filtre_type' => $this->translator->trans('media.mediatheque.filtre.type', domain: 'media'),
            'btn_order' => $this->translator->trans('media.mediatheque.btn.order', domain: 'media'),
            'order_asc' => $this->translator->trans('media.mediatheque.order.asc', domain: 'media'),
            'order_desc' => $this->translator->trans('media.mediatheque.order.desc', domain: 'media'),
        ];
    }
}
