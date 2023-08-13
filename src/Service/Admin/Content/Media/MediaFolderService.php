<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service qui gère les médiaFolder
 */

namespace App\Service\Admin\Content\Media;

use App\Entity\Admin\Content\MediaFolder;
use App\Repository\Admin\Content\MediaFolderRepository;
use App\Service\Admin\AppAdminService;
use App\Service\Admin\System\OptionSystemService;
use App\Utils\Content\Media\MediaFolderConst;
use App\Utils\System\Options\OptionSystemKey;
use App\Utils\Utils;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Integer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MediaFolderService extends AppAdminService
{

    private OptionSystemService $optionSystemService;

    /**
     * Path de la médiathèque
     * @var string
     */
    protected string $rootPathMedia = '';

    protected string $webPathMedia = '';

    /**
     * Path du projet
     * @var string
     */
    protected string $rootPath = '';

    protected string $rootPathThumbnail = '';

    protected string $webPathThumbnail = '';

    protected bool $canCreatePhysicalFolder = false;


    /**
     * Constructeur
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ContainerBagInterface  $containerBag,
        TranslatorInterface    $translator,
        UrlGeneratorInterface  $router,
        Security               $security,
        RequestStack           $requestStack,
        ParameterBagInterface  $parameterBag,
        OptionSystemService    $optionSystemService
    )
    {
        $this->optionSystemService = $optionSystemService;

        parent::__construct(
            $entityManager,
            $containerBag,
            $translator,
            $router,
            $security,
            $requestStack,
            $parameterBag
        );
        $this->initValue();
    }

    /**
     * Initialise des valeurs nécessaires au bon fonctionnement de la médiathèque
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function initValue(): void
    {
        $this->rootPath = $this->containerBag->get('kernel.project_dir');
        $mediaFolder = $this->optionSystemService->getValueByKey(OptionSystemKey::OS_MEDIA_PATH);
        $rootWebPath = $this->optionSystemService->getValueByKey(OptionSystemKey::OS_MEDIA_URL);

        //TODO gérer cas url externe

        $this->rootPathMedia = $this->rootPath . DIRECTORY_SEPARATOR .
            MediaFolderConst::ROOT_FOLDER_NAME . $mediaFolder;

        $this->webPathMedia = $rootWebPath . MediaFolderConst::PATH_WEB_PATH . $mediaFolder;
        $this->rootPathThumbnail = $this->rootPath . DIRECTORY_SEPARATOR . MediaFolderConst::ROOT_THUMBNAILS;
        $this->webPathThumbnail = $rootWebPath . MediaFolderConst::PATH_WEB_THUMBNAILS;

        $this->canCreatePhysicalFolder = filter_var($this->optionSystemService->getValueByKey(
            OptionSystemKey::OS_MEDIA_CREATE_PHYSICAL_FOLDER
        ), FILTER_VALIDATE_BOOLEAN);
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
     * Retoune l'arborescence sous la forme d'un array du dossier envoyé en paramètre
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
     */
    public function createMediaFolder(string $name, MediaFolder $parent = null): void
    {
        $mediaFolder = new MediaFolder();
        $mediaFolder->setName($name);
        $mediaFolder->setParent($parent);

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
     */
    public function updateMediaFolder(string $name, MediaFolder $mediaFolder): void
    {
        $oldName = $mediaFolder->getName();
        $mediaFolder->setName($name);
        $this->save($mediaFolder);

        /** @var MediaFolderRepository $repo */
        $repo = $this->getRepository(MediaFolder::class);
        $listMediaFolder = $repo->getAllByLikePath($oldName);

        /** @var MediaFolder $mediaFolder */
        $nb = count($listMediaFolder);
        $i = 0;
        $flush = false;
        foreach ($listMediaFolder as $mediaFolderParent) {
            $i++;
            if ($i === $nb) {
                $flush = true;
            }
            $mediaFolderParent->setPath(str_replace($oldName, $name, $mediaFolderParent->getPath()));
            $repo->save($mediaFolder, $flush);
        }

        $fileSystem = new Filesystem();

        $origin = $this->rootPathMedia . $mediaFolder->getPath() . DIRECTORY_SEPARATOR . $oldName;
        $target = $this->getPathFolder($mediaFolder);
        $fileSystem->rename($origin, $target);
    }

    /**
     * Retourne les informations d'un dossier
     * @param int $idFolderMedia
     * @return array
     */
    public function getInfoFolder(int $idFolderMedia): array
    {
        /** @var MediaFolder $mediaFolder */
        $mediaFolder = $this->findOneById(MediaFolder::class, $idFolderMedia);

        return [
            $this->translator->trans('media.mediatheque.info.folder.name', domain: 'media')
            => $mediaFolder->getName(),
            $this->translator->trans('media.mediatheque.info.folder.emplacement', domain: 'media')
            => 'emplacement',
            $this->translator->trans('media.mediatheque.info.folder.taille.disque', domain: 'media')
            => 'taille sur le disque',
            $this->translator->trans('media.mediatheque.info.folder.contenu', domain: 'media')
            => 'contenu',
            $this->translator->trans('media.mediatheque.info.folder.date_creation', domain: 'media')
            => 'date création'
            ];
    }
}
