<?php
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Service qui gère les médiaFolder
 */

namespace App\Service\Admin\Content\Media;

use App\Entity\Admin\Content\Media\Media;
use App\Entity\Admin\Content\Media\MediaFolder;
use App\Repository\Admin\Content\Media\MediaFolderRepository;
use App\Repository\Admin\Content\Media\MediaRepository;
use App\Service\Admin\AppAdminService;
use App\Service\Admin\GridService;
use App\Service\Admin\MarkdownEditorService;
use App\Service\Admin\System\OptionSystemService;
use App\Utils\Content\Media\MediaFolderConst;
use App\Utils\System\Options\OptionSystemKey;
use App\Utils\Utils;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MediaFolderService extends AppAdminService
{

    /**
     * Path de la médiathèque
     * @var string
     */
    protected string $rootPathMedia = '';

    /**
     * URL pour l'accès aux images depuis le web
     * @var string
     */
    protected string $webPathMedia = '';

    /**
     * Path du projet
     * @var string
     */
    protected string $rootPath = '';

    /**
     * Path des miniatures
     * @var string
     */
    protected string $rootPathThumbnail = '';

    /**
     * Url des miniatures
     * @var string
     */
    protected string $webPathThumbnail = '';

    /**
     * Option pour créer ou non un dossier physique
     * @var bool
     */
    protected bool $canCreatePhysicalFolder = true;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(#[AutowireLocator([
        'logger' => LoggerInterface::class,
        'entityManager' => EntityManagerInterface::class,
        'containerBag' => ContainerBagInterface::class,
        'translator' => TranslatorInterface::class,
        'router' => UrlGeneratorInterface::class,
        'security' => Security::class,
        'requestStack' => RequestStack::class,
        'parameterBag' => ParameterBagInterface::class,
        'optionSystemService' => OptionSystemService::class,
        'gridService' => GridService::class,
        'markdownEditorService' => MarkdownEditorService::class
    ])] protected ContainerInterface $handlers)
    {
        $this->initValue();
        parent::__construct($handlers);
    }

    /**
     * Initialise des valeurs nécessaires au bon fonctionnement de la médiathèque
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function initValue(): void
    {
        $optionSystemService = $this->getOptionSystemService();
        $containerBag = $this->getContainerBag();

        $this->rootPath = $containerBag->get('kernel.project_dir');
        $mediaFolder = $optionSystemService->getValueByKey(OptionSystemKey::OS_MEDIA_PATH);
        $rootWebPath = $optionSystemService->getValueByKey(OptionSystemKey::OS_MEDIA_URL);

        if($mediaFolder === null || $mediaFolder === '')
        {
            $mediaFolder = MediaFolderConst::NAME_DEFAULT_FOLDER_MEDIATHEQUE;
        }

        $env = $containerBag->get('kernel.environment');
        $this->rootPathThumbnail = $this->rootPath . DIRECTORY_SEPARATOR . MediaFolderConst::ROOT_THUMBNAILS;
        if($env === 'test') {
            $mediaFolder = MediaFolderConst::NAME_DEFAULT_FOLDER_MEDIATHEQUE_TEST;
            $this->rootPathThumbnail = $this->rootPath . DIRECTORY_SEPARATOR . MediaFolderConst::ROOT_THUMBNAILS . '-test';
        }

        //TODO gérer cas url externe
        $this->rootPathMedia = $this->rootPath . DIRECTORY_SEPARATOR .
            MediaFolderConst::ROOT_FOLDER_NAME . $mediaFolder;

        $this->webPathMedia = $rootWebPath . MediaFolderConst::PATH_WEB_PATH . $mediaFolder;

        $this->webPathThumbnail = $rootWebPath . MediaFolderConst::PATH_WEB_THUMBNAILS;

        $optCanCreatePhysicalFolder = $optionSystemService->getValueByKey(
            OptionSystemKey::OS_MEDIA_CREATE_PHYSICAL_FOLDER
        );

        if($optCanCreatePhysicalFolder !== null && $optCanCreatePhysicalFolder !== '')
        {
            $this->canCreatePhysicalFolder = filter_var($optCanCreatePhysicalFolder, FILTER_VALIDATE_BOOLEAN);
        }
    }

    /**
     * Supprime l'ensemble des dossiers / fichiers de la médiathèque
     * Recréer le dossier racine
     * Appeler uniquement pour les fixtures
     * @return void
     */
    public function resetAllMedia(): void
    {
        $filesystem = new Filesystem();
        if ($filesystem->exists($this->rootPathMedia)) {
            $filesystem->remove($this->rootPathMedia);
        }

        if ($filesystem->exists($this->rootPathThumbnail)) {
            $filesystem->remove($this->rootPathThumbnail);
        }

        $filesystem->mkdir($this->rootPathMedia);
        $filesystem->mkdir($this->rootPathThumbnail);
    }

    /**
     * Permet de créer le dossier mediaFolder physiquement
     * @param MediaFolder $mediaFolder
     * @return bool
     */
    public function createFolder(MediaFolder $mediaFolder): bool
    {
        if (!$this->canCreatePhysicalFolder) {
            return false;
        }

        $filesystem = new Filesystem();

        if ($mediaFolder->getParent() != null &&
            !$filesystem->exists($this->rootPathMedia . $mediaFolder->getPath())) {
            return $this->createFolder($mediaFolder->getParent());
        }

        $filesystem->mkdir($this->rootPathMedia . $mediaFolder->getPath() .
            DIRECTORY_SEPARATOR . $mediaFolder->getName());
        return true;
    }

    /**
     * Retourne le path complet du dossier envoyé en paramètre
     * chemin sous la forme chemin/vers/mon/dossier
     * @param MediaFolder $mediaFolder
     * @param bool $endDirectorySeparator si true ajoute DIRECTORY_SEPARATOR à la fin
     * @return string
     */
    public function getPathFolder(MediaFolder $mediaFolder, bool $endDirectorySeparator = true): string
    {
        $ds = '';
        if ($endDirectorySeparator) {
            $ds = DIRECTORY_SEPARATOR;
        }
        return $this->rootPathMedia . $mediaFolder->getPath() . DIRECTORY_SEPARATOR . $mediaFolder->getName() . $ds;
    }

    /** Retourne une liste de médiaFolder en fonction d'un médiaFolder
     * @param MediaFolder|null $mediaFolder
     * @param bool $trash
     * @param bool $disabled
     * @return float|int|mixed|string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getMediaFolderByMediaFolder
    (
        MediaFolder $mediaFolder = null,
        bool        $trash = false,
        bool        $disabled = false
    ): mixed
    {
        $repo = $this->getRepository(MediaFolder::class);

        /** @var MediaFolderRepository $repo */
        return $repo->getByMediaFolder($mediaFolder, $trash, $disabled);
    }

    /**
     * Retourne les données nécessaires sur le dossier courant pour la médiathèque
     * @param MediaFolder|null $mediaFolder
     * @return array
     */
    public function getMediaFolderInfo(MediaFolder $mediaFolder = null): array
    {
        $size = $this->getFolderSize($mediaFolder);
        $path = DIRECTORY_SEPARATOR;
        $root = [];
        $id = 0;
        if ($mediaFolder !== null) {
            $path = $mediaFolder->getPath() . DIRECTORY_SEPARATOR . $mediaFolder->getName();
            $root = $this->getTreeFolder($mediaFolder);
            $id = $mediaFolder->getId();
        }

        return [
            'root' => $root,
            'size' => Utils::getSizeName($size),
            'path' => $path,
            'id' => $id
        ];
    }

    /**
     * Retourne l'arborescence sous la forme d'un array du dossier envoyé en paramètre
     * @param MediaFolder $mediaFolder
     * @param array $tree
     * @return array
     */
    public function getTreeFolder(MediaFolder $mediaFolder, array $tree = []): array
    {
        array_unshift($tree, ['id' => $mediaFolder->getId(), 'name' => $mediaFolder->getName()]);
        if ($mediaFolder->getParent() !== null) {
            return $this->getTreeFolder($mediaFolder->getParent(), $tree);
        }
        return $tree;
    }

    /**
     * Retourne la taille d'un dossier
     * @param MediaFolder|null $mediaFolder
     * @return int
     */
    public function getFolderSize(MediaFolder $mediaFolder = null): int
    {
        $path = $this->rootPathMedia;
        if ($mediaFolder != null) {
            $path = $this->getPathFolder($mediaFolder, false);
        }
        return $this->folderSize($path);

    }

    /**
     * Calcul la taille d'un dossier
     * @param string $dir
     * @return int
     */
    private function folderSize(string $dir): int
    {
        $size = 0;
        foreach (glob(rtrim($dir, '/') . '/*', GLOB_NOSORT) as $each) {
            $size += is_file($each) ? filesize($each) : $this->folderSize($each);
        }
        return $size;
    }

    /**
     * Permet de créer un nouveau dossier en base de donnée ainsi que physiquement si l'option est activée
     * @param string $name
     * @param MediaFolder|null $parent
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function createMediaFolder(string $name, MediaFolder $parent = null): void
    {
        $mediaFolder = new MediaFolder();
        $mediaFolder->setName($name);
        $mediaFolder->setParent($parent);

        if($parent !== null) {
            $parent->addChild($mediaFolder);
        }


        $path = DIRECTORY_SEPARATOR;
        if ($parent !== null) {
            $path = $parent->getPath();
            if ($path === DIRECTORY_SEPARATOR) {
                $path .= $parent->getName();
            } else {
                $path = $path . DIRECTORY_SEPARATOR . $parent->getName();
            }
        }
        $mediaFolder->setPath($path);
        $this->save($mediaFolder);

        $this->createFolder($mediaFolder);
    }

    /**
     * Met à jour un média folder ainsi que tous ses enfants
     * @param string $name
     * @param MediaFolder $mediaFolder
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function updateMediaFolder(string $name, MediaFolder $mediaFolder): void
    {
        $oldName = $mediaFolder->getName();
        $mediaFolder->setName($name);

        $this->updateAllPathChildren($oldName, $name);

        $this->save($mediaFolder);

        if ($this->canCreatePhysicalFolder) {
            $fileSystem = new Filesystem();
            $origin = $this->rootPathMedia . $mediaFolder->getPath() . DIRECTORY_SEPARATOR . $oldName;
            $target = $this->getPathFolder($mediaFolder);
            $fileSystem->rename($origin, $target);
        }
    }

    /**
     * Met à jour tous les paths des dossiers enfants en remplaçant le nom du dossier old par new
     * met à jour tous les paths des médias contenu dans les dossiers enfants
     * @param string $old
     * @param string $new
     * @return void
     */
    private function updateAllPathChildren(string $old, string $new): void
    {
        $patternPath = '/\b' . preg_quote($old) . '\b/';
        if (stristr($old, "\/") === false) {
            $patternPath = '/' . preg_quote($old) . '/';
        }

        $newWebPath = $new;
        $patternWebPath = '/\b' . preg_quote($old) . '\b/';
        if (stristr($old, "/") === false) {
            $replace = str_replace('\\', '\/', $old);
            $patternWebPath = '/' . $replace . '/';
            $newWebPath = str_replace('\\', '/', $new);
        }

        /** @var MediaFolderRepository $repo */
        $repo = $this->getRepository(MediaFolder::class);
        $listMediaFolder = $repo->getAllByLikePath($old);

        /** @var MediaFolder $mediaFolderChildren */
        $nb = count($listMediaFolder);
        $i = 0;
        $flush = false;
        foreach ($listMediaFolder as $mediaFolderChildren) {

            $i++;
            if ($i === $nb) {
                $flush = true;
            }

            $mediaFolderChildren->setPath(preg_replace($patternPath, $new, $mediaFolderChildren->getPath()));
            $repo->save($mediaFolderChildren, $flush);
        }

        /** @var MediaRepository $repoM */
        $repoM = $this->getRepository(Media::class);
        $listeMedia = $repoM->getAllByLikePath($old);

        /** @var Media $media */
        $nb = count($listeMedia);

        $i = 0;
        $flush = false;
        foreach ($listeMedia as $media) {

            $i++;
            if ($i === $nb) {
                $flush = true;
            }
            $media->setPath(preg_replace($patternPath, preg_quote($new), $media->getPath()));
            $media->setWebPath(preg_replace($patternWebPath, $newWebPath, $media->getWebPath()));
            $repoM->save($media, $flush);
        }
    }

    /**
     * Retourne les informations d'un dossier
     * @param int $idFolderMedia
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getInfoFolder(int $idFolderMedia): array
    {
        $translator = $this->getTranslator();

        /** @var MediaFolder $mediaFolder */
        $mediaFolder = $this->findOneById(MediaFolder::class, $idFolderMedia);
        $content = $this->getContentFolder($mediaFolder);

        return [
            $translator->trans('media.mediatheque.info.folder.name', domain: 'media') => $mediaFolder->getName(),
            $translator->trans('media.mediatheque.info.folder.emplacement', domain: 'media') => $mediaFolder->getPath(),
            $translator->trans('media.mediatheque.info.folder.taille.disque', domain: 'media')
            => Utils::getSizeName($this->getFolderSize($mediaFolder)),
            $translator->trans('media.mediatheque.info.folder.contenu', domain: 'media') => $content['files'] . ' ' .
                $translator->trans('media.mediatheque.info.folder.files', domain: 'media') . ', ' .
                $content['directory'] . ' ' .
                $translator->trans('media.mediatheque.info.folder.folder', domain: 'media'),
            $translator->trans('media.mediatheque.info.folder.date_creation', domain: 'media')
            => $mediaFolder->getCreatedAt()->format('d/m/y H:i'),
            $translator->trans('media.mediatheque.info.folder.date_update', domain: 'media')
            => $mediaFolder->getUpdateAt()->format('d/m/y H:i')
        ];
    }

    /**
     * Retourne le contenu d'un dossier (nombre de dossiers et fichiers)
     * @param MediaFolder $mediaFolder
     * @return array
     */
    public function getContentFolder(MediaFolder $mediaFolder): array
    {
        $finder = new Finder();
        $path = $this->getPathFolder($mediaFolder);
        $nbDirectory = $finder->directories()->in($path)->count();
        $nbFile = $finder::create()->files()->name('*.*')->in($path)->count();

        return [
            'files' => $nbFile,
            'directory' => $nbDirectory
        ];
    }

    /**
     * Retourne l'ensemble des informations nécessaires pour la modale de déplacement
     * @param int $id
     * @param string $type
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllDataForModalMove(int $id, string $type = 'media'): array
    {
        $translator = $this->getTranslator();

        if ($type === 'media') {
            /** @var Media $entity */
            $entity = $this->findOneById(Media::class, $id);
            $folder = $entity->getMediaFolder();

            $label = $translator->trans('media.mediatheque.move.label.media',
                ['name' => $entity->getName()], domain: 'media');

        } else {
            /** @var MediaFolder $folder */
            $folder = $this->findOneById(MediaFolder::class, $id);
            $label = $translator->trans('media.mediatheque.move.label.folder',
                ['name' => $folder->getName()], domain: 'media');
        }

        $return = [];

        $parentId = 0;
        if ($folder != null && $folder->getParent() != null) {
            $parentId = $folder->getParent()->getId();
        }
        $return['parentId'] = $parentId;
        $return['label'] = $label;
        $return['liste'] = $this->getListeFolderToMove($folder);
        return $return;
    }

    /**
     * Retourne une liste de dossier sous la forme id => nom valide pour un déplacement
     * en fonction du dossier courant
     * @param MediaFolder|null $folder
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getListeFolderToMove(MediaFolder $folder = null): array
    {

        /** @var MediaFolderRepository $repo */
        $repo = $this->getRepository(MediaFolder::class);
        $result = $repo->getAllFolderNoChild($folder);

        $parents = [];
        /** @var MediaFolder $folder */
        foreach ($result as $folder) {
            if ($folder->getParent() === null) {
                $parents[] = $folder;
            }
        }

        $return = [];
        $return[] = ['id' => -1, 'name' => 'root'];
        foreach ($parents as $parent) {
            $return = $this->getTreeFolders($parent, $result, $return, 1);
        }
        return $return;
    }

    /**
     * Construit de façon récursive un arbre de dossier à afficher sous forme de listing
     * @param MediaFolder $folder
     * @param array $folders
     * @param array $return
     * @param int $depth
     * @return array
     */
    private function getTreeFolders(MediaFolder $folder, array $folders, array $return, int $depth): array
    {
        if ($folder->getParent() === null) {
            $return[] = ['id' => $folder->getId(), 'name' => $folder->getName()];
        }
        if ($folder->getChildren()->count() > 0) {
            foreach ($folder->getChildren() as $child) {
                foreach ($folders as $fold) {
                    if ($fold->getId() === $child->getId()) {
                        $return[] = ['id' => $child->getId(), 'name' => '|' . str_pad($child->getName(),
                                strlen($child->getName()) + $depth
                                , "-", STR_PAD_LEFT)];
                        break;
                    }
                }
                if ($child->getChildren()->count() > 0) {
                    $depth = $depth + 1;
                    $return = $this->getTreeFolders($child, $folders, $return, $depth);
                    $depth = $depth - 1;
                }
            }
        }
        return $return;
    }

    /**
     * Déplacer un dossier vers le dossier en paramètre
     * @param MediaFolder $mediaFolder
     * @param MediaFolder|null $newParent
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function moveFolder(MediaFolder $mediaFolder, MediaFolder $newParent = null): void
    {
        $oldPath = $mediaFolder->getPath();
        $old = DIRECTORY_SEPARATOR . $mediaFolder->getName();
        if ($mediaFolder->getParent() !== null) {
            $oldParent = $mediaFolder->getParent();
            $old = $oldParent->getPath() . $oldParent->getName() . DIRECTORY_SEPARATOR . $mediaFolder->getName();
            if ($oldParent->getPath() !== DIRECTORY_SEPARATOR) {
                $old = $oldParent->getPath() . DIRECTORY_SEPARATOR .
                    $oldParent->getName() . DIRECTORY_SEPARATOR . $mediaFolder->getName();
            }
        }

        $path = DIRECTORY_SEPARATOR;
        $new = DIRECTORY_SEPARATOR . $mediaFolder->getName();
        if ($newParent !== null) {
            $path = $newParent->getPath();
            if ($path === DIRECTORY_SEPARATOR) {
                $path .= $newParent->getName();
            } else {
                $path = $path . DIRECTORY_SEPARATOR . $newParent->getName();
            }
            $new = $newParent->getPath() . $newParent->getName() . DIRECTORY_SEPARATOR . $mediaFolder->getName();
            if ($newParent->getPath() !== DIRECTORY_SEPARATOR) {
                $new = $newParent->getPath() . DIRECTORY_SEPARATOR .
                    $newParent->getName() . DIRECTORY_SEPARATOR . $mediaFolder->getName();
            }
        }

        $mediaFolder->setPath($path);
        $mediaFolder->setParent($newParent);
        $newParent->addChild($mediaFolder);
        $this->save($mediaFolder);

        $this->updateAllPathChildren($old, $new);

        if ($this->canCreatePhysicalFolder) {
            $fileSystem = new Filesystem();
            $origin = $this->rootPathMedia . $oldPath . DIRECTORY_SEPARATOR . $mediaFolder->getName();
            $target = $this->getPathFolder($mediaFolder);
            $fileSystem->rename($origin, $target);
        }
    }

    /**
     * Retourne le path média
     * @return string
     */
    public function getRootPathMedia(): string
    {
        return $this->rootPathMedia;
    }

    /**
     * Retourne le path thumbnail
     * @return string
     */
    public function getRootPathThumbnail(): string
    {
        return $this->rootPathThumbnail;
    }
}
