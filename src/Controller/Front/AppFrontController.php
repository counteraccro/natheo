<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 *
 */

namespace App\Controller\Front;

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

    public function __construct(
        #[AutowireLocator([
            'optionSystemFront' => OptionSystemFrontService::class,
        ])]
        private readonly ContainerInterface $handlers
    )
    {
        $this->optionSystemFrontService = $this->handlers->get('optionSystemFront');
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
}
