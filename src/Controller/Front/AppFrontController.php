<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 *
 */

namespace App\Controller\Front;

use App\Service\Admin\Content\Page\PageService;
use App\Service\Front\OptionSystemFrontService;
use App\Service\Installation\InstallationService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;

class AppFrontController extends AbstractController
{
    /**
     * @var OptionSystemFrontService
     */
    protected OptionSystemFrontService $optionSystemFrontService;

    /**
     * @var PageService
     */
    protected PageService $pageService;

    /**
     * @var InstallationService
     */
    protected InstallationService $installationService;

    protected EntityManagerInterface $entityManager;

    public function __construct(
        #[
            AutowireLocator([
                'optionSystemFront' => OptionSystemFrontService::class,
                'pageService' => PageService::class,
                'installationService' => InstallationService::class,
                'entityManager' => EntityManagerInterface::class,
            ]),
        ]
        private readonly ContainerInterface $handlers,
    ) {
        $this->optionSystemFrontService = $this->handlers->get('optionSystemFront');
        $this->pageService = $this->handlers->get('pageService');
        $this->installationService = $this->handlers->get('installationService');
        $this->entityManager = $this->handlers->get('entityManager');
    }

    /**
     * Retourne le template en fonction de l'option system OS_THEME_FRONT_SITE
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getPathTemplate(): string
    {
        return $this->optionSystemFrontService->getPathTemplate();
    }

    /**
     * Retourne la cle scriptTag pour les scripts et css côté front, si template null, retourne le template courant
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getScriptTags(): string
    {
        return $this->optionSystemFrontService->getScriptTags();
    }

    /**
     * Return true si le site est ouvert, false sinon
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function isOpenSite(): bool
    {
        return $this->optionSystemFrontService->isOpenSite();
    }

    /**
     * Retourne un entityManager en fonction de son entité
     * @param string $entity
     * @return EntityRepository
     */
    public function getRepository(string $entity): EntityRepository
    {
        return $this->entityManager->getRepository($entity);
    }
}
