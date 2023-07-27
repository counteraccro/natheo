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
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MediaFolderService extends AppAdminService
{

    private OptionSystemService $optionSystemService;

    protected string $rootPathMedia = '';

    protected string $webPathMedia = '';

    protected string $rootPath = '';

    protected string $rootThumbnailsPath = '';

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
        $this->rootThumbnailsPath = $this->rootPath . DIRECTORY_SEPARATOR . MediaFolderConst::ROOT_THUMBNAILS;

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

        if ($filesystem->exists($this->rootThumbnailsPath)) {
            $filesystem->remove($this->rootThumbnailsPath);
        }

        echo $this->rootThumbnailsPath;

        $filesystem->mkdir($this->rootPathMedia);
        $filesystem->mkdir($this->rootThumbnailsPath);


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
}
