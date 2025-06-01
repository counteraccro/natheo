<?php

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Class globale pour les extensions Twig
 */
namespace App\Twig\Extension;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AppExtension
{
    /**
     * @var RouterInterface
     */
    protected RouterInterface $router;

    /**
     * @var TranslatorInterface
     */
    protected TranslatorInterface $translator;

    /**
     * @param ContainerInterface $handlers
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        #[AutowireLocator([
            'translator' => TranslatorInterface::class,
            'router' => RouterInterface::class,
        ])]
        private readonly ContainerInterface $handlers
    )
    {
        $this->router = $this->handlers->get('router');
        $this->translator = $this->handlers->get('translator');
    }
}
