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
use App\Utils\Content\Media\Thumbnail;
use App\Utils\System\Options\OptionUserKey;
use App\Utils\System\User\PersonalData;
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

                $pathMedia = str_replace($this->rootPathMedia, '', $file->getPath()) .
                    DIRECTORY_SEPARATOR . $file->getFilename();

                $thumbnail = new Thumbnail($this->rootPathThumbnail);
                $nameThumbnail = $thumbnail->create($file->getRealPath(), $file->getExtension());

                $media->setThumbnail($nameThumbnail);
                $media->setExtension($file->getExtension());
                $media->setPath($pathMedia);
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
     * Renvoi l'url web de la miniature
     * @param string $thumbnail
     * @return string
     */
    public function getWebPathThumbnail(string $thumbnail): string
    {
        return $this->webPathThumbnail . $thumbnail;
    }

    /**
     * Retourne l'ensemble des médias et dossier lié à un dossier trié et ordonné en fonction
     * des paramètres
     * @param MediaFolder|null $mediaFolder
     * @param string $filter
     * @param string $order
     * @return array
     */
    public function getALlMediaAndMediaFolderByMediaFolder
    (
        MediaFolder $mediaFolder = null,
        string      $filter = 'created_at',
        string      $order = 'asc'
    ): array
    {
        $medias = $this->getMediaByMediaFolder($mediaFolder);
        $folders = $this->getMediaFolderByMediaFolder($mediaFolder);

        $return = [];

        /** @var Media $media */
        foreach ($medias as $media) {
            $return[] = [
                'type' => 'media',
                'id' => $media->getId(),
                'name' => $media->getName(),
                'description' => $media->getDescription(),
                'size' => Utils::getSizeName($media->getSize()),
                'webPath' => $media->getWebPath(),
                'thumbnail' => $this->getWebPathThumbnail($media->getThumbnail()),
                'created_at' => $media->getCreatedAt()->getTimestamp()
            ];
        }

        /** @var MediaFolder $folder */
        foreach ($folders as $folder) {
            $return[] = [
                'type' => 'folder',
                'id' => $folder->getId(),
                'name' => $folder->getName(),
                'created_at' => $folder->getCreatedAt()->getTimestamp()
            ];
        }

        $column = array_column($return, $filter);

        $sort = SORT_ASC;
        if ($order === 'desc') {
            $sort = SORT_DESC;
        }
        array_multisort($column, $sort, $return);

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
     * Retourne les informations d'un média
     * @param int $idMedia
     * @return array
     */
    public function getInfoMedia(int $idMedia): array
    {
        /** @var Media $media */
        $media = $this->findOneById(Media::class, $idMedia);
        $user = $media->getUser();
        $persoalDataRender = $user->getOptionUserByKey(OptionUserKey::OU_DEFAULT_PERSONAL_DATA_RENDER);
        $personalData = new PersonalData($user, $persoalDataRender->getValue());

        return [
            'data' => [
                $this->translator->trans('media.mediatheque.info.media.name', domain: 'media')
                => $media->getName(),
                $this->translator->trans('media.mediatheque.info.media.description', domain: 'media')
                => $media->getDescription(),
                $this->translator->trans('media.mediatheque.info.media.extension', domain: 'media')
                => $media->getExtension(),
                $this->translator->trans('media.mediatheque.info.media.user', domain: 'media')
                => $personalData->getPersonalData(),
                $this->translator->trans('media.mediatheque.info.media.emplacement', domain: 'media')
                => $media->getPath(),
                $this->translator->trans('media.mediatheque.info.media.size', domain: 'media')
                => Utils::getSizeName($media->getSize()),
                $this->translator->trans('media.mediatheque.info.media.date_created', domain: 'media')
                => $media->getCreatedAt()->format('d/m/y H:i'),
                $this->translator->trans('media.mediatheque.info.media.date_update', domain: 'media')
                => $media->getUpdateAt()->format('d/m/y H:i'),
            ],
            'thumbnail' => $this->getWebPathThumbnail($media->getThumbnail()),
            'web_path' => $media->getWebPath()
        ];
    }

    /**
     * Ajoute une image physiquement sur le disque et créer l'objet Media
     * @return void
     */
    public function uploadMediaFile(int $idFolder, array $file): void
    {
        /** @var MediaFolder $folder */
        $folder = $this->findOneById(MediaFolder::class, $idFolder);
        $path = $this->rootPathMedia;
        if($folder != null && $this->canCreatePhysicalFolder)
        {
            $path = $this->getPathFolder($folder, true);
        }
        $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $file['url']));
        //file_put_contents($path . 'oki.' . $file['fileExtention'], $data);

        echo $path;

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
            'media' => [
                'link_info' => $this->translator->trans('media.mediatheque.media.link.info', domain: 'media'),
                'link_edit' => $this->translator->trans('media.mediatheque.media.link.edit', domain: 'media'),
                'link_move' => $this->translator->trans('media.mediatheque.media.link.move', domain: 'media'),
                'link_remove' => $this->translator->trans('media.mediatheque.media.link.remove', domain: 'media'),
            ],
            'folder' => [
                'new' => $this->translator->trans('media.mediatheque.folder.new', domain: 'media'),
                'edit' => $this->translator->trans('media.mediatheque.folder.edit', domain: 'media'),
                'input_label' => $this->translator->trans('media.mediatheque.folder.input.label', domain: 'media'),
                'input_label_placeholder' =>
                    $this->translator->trans('media.mediatheque.folder.input.label.placeholder', domain: 'media'),
                'input_error' => $this->translator->trans('media.mediatheque.folder.input.error', domain: 'media'),
                'btn_submit_create' =>
                    $this->translator->trans('media.mediatheque.folder.btn.submit.create', domain: 'media'),
                'btn_submit_edit' =>
                    $this->translator->trans('media.mediatheque.folder.btn.submit.edit', domain: 'media'),
                'btn_cancel' => $this->translator->trans('media.mediatheque.folder.btn.cancel', domain: 'media'),
                'msg_wait_create' =>
                    $this->translator->trans('media.mediatheque.folder.msg.wait.create', domain: 'media'),
                'msg_wait_edit' =>
                    $this->translator->trans('media.mediatheque.folder.msg.wait.edit', domain: 'media'),
            ],
            'info' => [
                'title' => $this->translator->trans('media.mediatheque.info.title', domain: 'media'),
                'btn_close' => $this->translator->trans('media.mediatheque.info.btn.close', domain: 'media'),
                'link_size' => $this->translator->trans('media.mediatheque.info.link.size', domain: 'media'),
            ],
            'upload' => [
                'title' => $this->translator->trans('media.mediatheque.upload.title', domain: 'media'),
                'help' => $this->translator->trans('media.mediatheque.upload.help', domain: 'media'),
                'input_upload' => $this->translator->trans('media.mediatheque.upload.input.upload', domain: 'media'),
                'input_title' => $this->translator->trans('media.mediatheque.upload.input.title', domain: 'media'),
                'input_description' =>
                    $this->translator->trans('media.mediatheque.upload.input.description', domain: 'media'),
                'btn_cancel' => $this->translator->trans('media.mediatheque.upload.btn.cancel', domain: 'media'),
                'btn_upload' => $this->translator->trans('media.mediatheque.upload.btn.upload', domain: 'media'),
                'error_title' => $this->translator->trans('media.mediatheque.upload.error.title', domain: 'media'),
                'error_size' => $this->translator->trans('media.mediatheque.upload.error.size', domain: 'media'),
                'error_ext' => $this->translator->trans('media.mediatheque.upload.error.ext', domain: 'media'),
            ]
        ];
    }
}
