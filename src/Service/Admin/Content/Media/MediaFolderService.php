<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service qui gère les médiaFolder
 */

namespace App\Service\Admin\Content\Media;

use App\Entity\Admin\Content\MediaFolder;
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
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MediaFolderService extends AppAdminService
{

    private OptionSystemService $optionSystemService;

    private string $rootPathMedia = '';

    private bool $canCreatePhysicalFolder = false;


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
        $rootPath = $this->containerBag->get('kernel.project_dir');
        $publicFolder = $this->optionSystemService->getValueByKey(OptionSystemKey::OS_MEDIA_PATH);

        //TODO gérer cas url externe

        $this->rootPathMedia = $rootPath . DIRECTORY_SEPARATOR .
            MediaFolderConst::ROOT_FOLDER_NAME . DIRECTORY_SEPARATOR .
            $publicFolder . DIRECTORY_SEPARATOR .
            MediaFolderConst::ROOT_MEDIA_FOLDER_NAME;

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
        $filesystem->mkdir($this->rootPathMedia);
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
}
