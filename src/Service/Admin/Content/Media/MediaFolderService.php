<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service qui gère les médiaFolder
 */

namespace App\Service\Admin\Content\Media;

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
    }

    /**
     * Supprime l'ensemble des dossiers / fichiers de la médiathèque
     * Appeler uniquement pour les fixtures
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function removeAllMedia(): void
    {
        $rootPath = $this->containerBag->get('kernel.project_dir');
        $publicFolder = $this->optionSystemService->getValueByKey(OptionSystemKey::OS_MEDIA_PATH);

        $path = $rootPath . DIRECTORY_SEPARATOR . $publicFolder . DIRECTORY_SEPARATOR . MediaFolderConst::ROOT_FOLDER;

        $filesystem = new Filesystem();
        if($filesystem->exists($path))
        {
            $filesystem->remove($path);
        }
    }
}
