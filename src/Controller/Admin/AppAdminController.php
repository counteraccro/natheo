<?php

namespace App\Controller\Admin;

use App\Service\Admin\System\OptionUserService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;

class AppAdminController extends AbstractController
{
    /**
     * Filtre pour n'afficher que mes données
     * @var string
     */
    const string FILTER_ME = 'me';

    /**
     * @var OptionUserService
     */
    protected OptionUserService $optionUserService;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @param ContainerInterface $handlers
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        #[
            AutowireLocator([
                'optionUserService' => OptionUserService::class,
                'logger' => LoggerInterface::class,
            ]),
        ]
        private readonly ContainerInterface $handlers,
    ) {
        $this->optionUserService = $this->handlers->get('optionUserService');
        $this->logger = $this->handlers->get('logger');
    }
}
