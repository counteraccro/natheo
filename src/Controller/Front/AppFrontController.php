<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 *
 */

namespace App\Controller\Front;

use App\Service\Admin\Content\Page\PageService;
use App\Service\Front\OptionSystemFrontService;
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

    protected PageService $pageService;

    public function __construct(
        #[AutowireLocator([
            'optionSystemFront' => OptionSystemFrontService::class,
            'pageService' => PageService::class,
        ])]
        private readonly ContainerInterface $handlers
    )
    {
        $this->optionSystemFrontService = $this->handlers->get('optionSystemFront');
        $this->pageService = $this->handlers->get('pageService');
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
     * Return true si le site est ouvert, false sinon
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function isOpenSite(): bool
    {
        return $this->optionSystemFrontService->isOpenSite();
    }
}
