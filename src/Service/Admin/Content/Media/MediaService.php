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
use App\Utils\Content\Media\MediaConst;
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

                // Cas étrange de fichier qui remonte avec un path commençant par //
                if (preg_match('/^\\\\\\\\/m', $pathMedia)) {
                    $pathMedia = str_replace('\\\\', '\\', $pathMedia);
                }

                $nameThumbnail = null;
                $type = MediaConst::MEDIA_TYPE_FILE;
                if (in_array($file->getExtension(), Thumbnail::EXT_ALLOW_THUMBNAIL)) {
                    $thumbnail = new Thumbnail($this->rootPathThumbnail);
                    $nameThumbnail = $thumbnail->create($file->getRealPath(), $file->getExtension());
                    $type = MediaConst::MEDIA_TYPE_IMG;
                }

                $media->setThumbnail($nameThumbnail);
                $media->setExtension($file->getExtension());
                $media->setPath($pathMedia);
                $media->setTitle($file->getFilenameWithoutExtension());
                $media->setName($file->getFilename());
                $media->setWebPath($this->getWebPath($media));
                $media->setSize($file->getSize());
                $media->setType($type);
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
        $path = '/' . $media->getName();
        if ($mediaFolder != null) {
            $path = $mediaFolder->getPath() . '/' .
                $mediaFolder->getName() . '/' . $media->getName();
        }

        $path = str_replace('\\', '/', $path);
        $path = str_replace('//', '/', $path);
        return $this->webPathMedia . $path;
    }

    /**
     * Génère le path d'un média
     * @param Media $media
     * @return string
     */
    public function getPath(Media $media): string
    {
        $mediaFolder = $media->getMediaFolder();
        $path = DIRECTORY_SEPARATOR . $media->getName();
        if ($mediaFolder != null) {
            $path = $mediaFolder->getPath() .
                $mediaFolder->getName() . DIRECTORY_SEPARATOR . $media->getName();
        }
        return str_replace('//', '/', $path);
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
                'thumbnail' => $this->getThumbnail($media),
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
        $personalDataRender = $user->getOptionUserByKey(OptionUserKey::OU_DEFAULT_PERSONAL_DATA_RENDER);
        $personalData = new PersonalData($user, $personalDataRender->getValue());

        return [
            'data' => [
                $this->translator->trans('media.mediatheque.info.media.name', domain: 'media')
                => $media->getName(),
                $this->translator->trans('media.mediatheque.info.media.titre', domain: 'media')
                => $media->getTitle(),
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
            'thumbnail' => $this->getThumbnail($media),
            'web_path' => $media->getWebPath(),
            'media_type' => $media->getType()
        ];
    }

    /**
     * Ajoute une image physiquement sur le disque et créer l'objet Média
     * @param int $idFolder
     * @param array $file
     * @return void
     */
    public function uploadMediaFile(int $idFolder, array $file): void
    {
        /** @var MediaFolder $folder */
        $folder = $this->findOneById(MediaFolder::class, $idFolder);
        $path = $this->rootPathMedia . DIRECTORY_SEPARATOR;
        if ($folder != null && $this->canCreatePhysicalFolder) {
            $path = $this->getPathFolder($folder);
        }

        list(, $data) = explode(';', $file['url']);
        list(, $data) = explode(',', $data);
        $data = base64_decode($data);


        $name = $file['name'] . '-' . time() . '.' . $file['fileExtention'];
        file_put_contents($path . $name, $data);

        $media = new Media();
        $media->setMediaFolder($folder);
        $media->setUser($this->security->getUser());
        $this->UpdateMediaFile($media, $name);
        $media->setName($file['name']);
        $media->setDescription($file['description']);
        $this->save($media);
    }

    /**
     * Retourne le bon thumbnail en fonction du media
     * @param Media $media
     * @return string
     */
    public function getThumbnail(Media $media): string
    {
        if ($media->getType() === MediaConst::MEDIA_TYPE_IMG) {
            return $this->getWebPathThumbnail($media->getThumbnail());
        }

        return match ($media->getExtension()) {
            'pdf' => MediaFolderConst::PATH_WEB_NATHEO_MEDIA . 'file_pdf.png',
            'xls', 'xlsx' => MediaFolderConst::PATH_WEB_NATHEO_MEDIA . 'file_xls.png',
            'csv' => MediaFolderConst::PATH_WEB_NATHEO_MEDIA . 'file_csv.png',
            'doc', 'docx' => MediaFolderConst::PATH_WEB_NATHEO_MEDIA . 'file_doc.png',
            default => MediaFolderConst::PATH_WEB_NATHEO_MEDIA . 'file.png',
        };
    }

    /**
     * Déplace un type de média vers le MédiaFolder idToMove
     * @param int $id
     * @param string $type
     * @param int $idToMove
     * @return void
     */
    public function move(int $id, string $type, int $idToMove): void
    {
        if ($type === 'media') {
            $media = $this->findOneById(Media::class, $id);
            $folder = $this->findOneById(MediaFolder::class, $idToMove);
            $this->moveMedia($media, $folder);

        } else {
            $folder = $this->findOneById(MediaFolder::class, $id);
            $folderInMove = $this->findOneById(MediaFolder::class, $idToMove);
            $this->moveFolder($folder, $folderInMove);
        }
    }

    /**
     * Déplace un média vers le médiaFolder en paramètre
     * @param Media $media
     * @param MediaFolder|null $mediaFolderInMove
     * @return void
     */
    public function moveMedia(Media $media, MediaFolder $mediaFolderInMove = null): void
    {
        $oldPath = $this->rootPathMedia . DIRECTORY_SEPARATOR . $media->getName();
        if($media->getMediaFolder() !== null)
        {
            $oldPath = $this->getPathFolder($media->getMediaFolder()) . $media->getName();
        }

        $media->setMediaFolder($mediaFolderInMove);
        $media->setWebPath($this->getWebPath($media));
        $media->setPath($this->getPath($media));
        $this->save($media);

        if($this->canCreatePhysicalFolder)
        {
            $newPath = $this->rootPathMedia . DIRECTORY_SEPARATOR . $media->getName();
            if($media->getMediaFolder() !== null)
            {
                $newPath = $this->getPathFolder($media->getMediaFolder()) . $media->getName();
            }

            $fileSystem = new Filesystem();
            $fileSystem->rename($oldPath, $newPath, true);
        }
    }

    /**
     * Retourne un tableau contenant le nombre de médias et le nombre de dossiers
     * dans la corbeille
     * @return array
     */
    public function getNbInTrash(): array
    {
        $repoMedia = $this->getRepository(Media::class);
        $return['medias'] = $repoMedia->getNbInTrash();

        $repoMediaFold = $this->getRepository(MediaFolder::class);
        $return['folders'] = $repoMediaFold->getNbInTrash();

        return $return;
    }

    public function getAllMediaAndMediaFolderInTrash()
    {
        $repoMedia = $this->getRepository(Media::class);
        $medias = $repoMedia->findBy(['trash' => true]);

        $repoMediaFolder = $this->getRepository(MediaFolder::class);
        $folders = $repoMediaFolder->findBy(['trash' => true]);


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
                'thumbnail' => $this->getThumbnail($media),
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

        return $return;

    }

    /**
     * Met à jour le champ corbeille en fonction de $trash d'un média ou d'un mediaFolder
     * @param string $type
     * @param int $id
     * @param bool $trash
     * @return void
     */
    public function updateTrash(string $type, int $id, bool $trash): void
    {
        if ($type === 'media') {
            /** @var Media $media */
            $media = $this->findOneById(Media::class, $id);
            $media->setTrash($trash);
            $this->save($media);
        } else {
            /** @var MediaFolder $folder */
            $folder = $this->findOneById(MediaFolder::class, $id);
            $folder->setTrash($trash);
            $this->save($folder);
        }
    }



    /**
     * Retourne la traduction pour la médiathèque
     * @return array
     */
    public function getMediathequeTranslation(): array
    {
        return [
            'loading' => $this->translator->trans('media.mediatheque.loading', domain: 'media'),
            'btn_new_folder' => $this->translator->trans('media.mediatheque.btn.new.folder', domain: 'media'),
            'btn_new_media' => $this->translator->trans('media.mediatheque.btn.new.media', domain: 'media'),
            'btn_filtre' => $this->translator->trans('media.mediatheque.btn.filtre', domain: 'media'),
            'filtre_date' => $this->translator->trans('media.mediatheque.filtre.date', domain: 'media'),
            'filtre_nom' => $this->translator->trans('media.mediatheque.filtre.nom', domain: 'media'),
            'filtre_type' => $this->translator->trans('media.mediatheque.filtre.type', domain: 'media'),
            'order_asc' => $this->translator->trans('media.mediatheque.order.asc', domain: 'media'),
            'order_desc' => $this->translator->trans('media.mediatheque.order.desc', domain: 'media'),
            'search_placeholder' => $this->translator->trans('media.mediatheque.search.placeholder', domain: 'media'),
            'media' => [
                'link_info' => $this->translator->trans('media.mediatheque.media.link.info', domain: 'media'),
                'link_edit' => $this->translator->trans('media.mediatheque.media.link.edit', domain: 'media'),
                'link_edit_media' =>
                    $this->translator->trans('media.mediatheque.media.link.edit.media', domain: 'media'),
                'link_move' => $this->translator->trans('media.mediatheque.media.link.move', domain: 'media'),
                'link_remove' => $this->translator->trans('media.mediatheque.media.link.remove', domain: 'media'),
                'no_media' => $this->translator->trans('media.mediatheque.media.no.media', domain: 'media'),
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
                'link_download' => $this->translator->trans('media.mediatheque.info.link.download', domain: 'media'),
            ],
            'upload' => [
                'title' => $this->translator->trans('media.mediatheque.upload.title', domain: 'media'),
                'help' => $this->translator->trans('media.mediatheque.upload.help', domain: 'media'),
                'form_title' => $this->translator->trans('media.mediatheque.upload.form.title', domain: 'media'),
                'input_upload' => $this->translator->trans('media.mediatheque.upload.input.upload', domain: 'media'),
                'btn_close' => $this->translator->trans('media.mediatheque.upload.btn.close', domain: 'media'),
                'input_title' => $this->translator->trans('media.mediatheque.upload.input.title', domain: 'media'),
                'input_title_help' =>
                    $this->translator->trans('media.mediatheque.upload.input.title.help', domain: 'media'),
                'input_description' =>
                    $this->translator->trans('media.mediatheque.upload.input.description', domain: 'media'),
                'input_description_help' =>
                    $this->translator->trans('media.mediatheque.upload.input.description.help', domain: 'media'),
                'btn_cancel' => $this->translator->trans('media.mediatheque.upload.btn.cancel', domain: 'media'),
                'btn_upload' => $this->translator->trans('media.mediatheque.upload.btn.upload', domain: 'media'),
                'error_title' => $this->translator->trans('media.mediatheque.upload.error.title', domain: 'media'),
                'error_size' => $this->translator->trans('media.mediatheque.upload.error.size', domain: 'media'),
                'error_ext' => $this->translator->trans('media.mediatheque.upload.error.ext', domain: 'media'),
                'no_preview' => $this->translator->trans('media.mediatheque.upload.no_preview', domain: 'media'),
                'preview' => $this->translator->trans('media.mediatheque.upload.preview', domain: 'media'),
                'preview_help' => $this->translator->trans('media.mediatheque.upload.preview.help', domain: 'media'),
                'loading_msg' => $this->translator->trans('media.mediatheque.upload.loading.msg', domain: 'media'),
                'loading_msg_success' =>
                    $this->translator->trans('media.mediatheque.upload.loading.msg.success', domain: 'media'),
            ],
            'edit_media' => [
                'title' => $this->translator->trans('media.mediatheque.edit.media.title', domain: 'media'),
                'legend' => $this->translator->trans('media.mediatheque.edit.media.legend', domain: 'media'),
                'media_name' => $this->translator->trans('media.mediatheque.edit.media.name', domain: 'media'),
                'media_name_placeholder' =>
                    $this->translator->trans('media.mediatheque.edit.media.name.placeholder', domain: 'media'),
                'media_description' =>
                    $this->translator->trans('media.mediatheque.edit.media.description', domain: 'media'),
                'media_description_placeholder' =>
                    $this->translator->trans('media.mediatheque.edit.media.description.placeholder', domain: 'media'),
                'submit' => $this->translator->trans('media.mediatheque.edit.media.submit', domain: 'media'),
                'cancel' => $this->translator->trans('media.mediatheque.edit.media.cancel', domain: 'media'),
                'loading' => $this->translator->trans('media.mediatheque.edit.media.loading', domain: 'media'),
                'success' => $this->translator->trans('media.mediatheque.edit.media.success', domain: 'media'),
            ],
            'move' => [
                'title' => $this->translator->trans('media.mediatheque.move.title', domain: 'media'),
                'btn_close' => $this->translator->trans('media.mediatheque.move.btn.close', domain: 'media'),
                'btn_validate' => $this->translator->trans('media.mediatheque.move.btn.validate', domain: 'media'),
                'input_label' => $this->translator->trans('media.mediatheque.move.input.label', domain: 'media'),
                'input_info' => $this->translator->trans('media.mediatheque.move.input.info', domain: 'media'),
                'loading' => $this->translator->trans('media.mediatheque.move.loading', domain: 'media'),
                'success' => $this->translator->trans('media.mediatheque.move.success', domain: 'media')
            ],
            'trash' => [
                'title' => $this->translator->trans('media.mediatheque.trash.title', domain: 'media'),
                'text_folder' => $this->translator->trans('media.mediatheque.trash.text.folder', domain: 'media'),
                'text_media' => $this->translator->trans('media.mediatheque.trash.text.media', domain: 'media'),
                'text_info' => $this->translator->trans('media.mediatheque.trash.text.info', domain: 'media'),
                'loading' => $this->translator->trans('media.mediatheque.trash.loading', domain: 'media'),
                'btn_confirm' => $this->translator->trans('media.mediatheque.trash.btn.confirm', domain: 'media'),
                'btn_cancel' => $this->translator->trans('media.mediatheque.trash.btn.cancel', domain: 'media'),
                'success_trash' => $this->translator->trans('media.mediatheque.trash.success.trash', domain: 'media'),
                'success_no_trash' =>
                    $this->translator->trans('media.mediatheque.trash.success.no.trash', domain: 'media'),
                'breadcrumb_trash' =>
                    $this->translator->trans('media.mediatheque.trash.breadcrumb', domain: 'media'),
                'btn_close_trash' => $this->translator->trans('media.mediatheque.trash.btn.close', domain: 'media'),
                'link_revert' => $this->translator->trans('media.mediatheque.trash.link.revert', domain: 'media'),
                'link_delete' => $this->translator->trans('media.mediatheque.trash.link.delete', domain: 'media'),
                'no_media' => $this->translator->trans('media.mediatheque.trash.empty.media', domain: 'media'),
            ]
        ];
    }
}
