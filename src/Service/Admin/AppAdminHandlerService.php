<?php

namespace App\Service\Admin;

use App\Service\Admin\System\OptionSystemService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AppAdminHandlerService
{
    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $entityManager;

    /**
     * Paramètre globaux de Symfony
     * @var ContainerBagInterface
     */
    protected ContainerBagInterface $containerBag;

    /**
     * @var TranslatorInterface
     */
    protected TranslatorInterface $translator;

    /**
     * @var UrlGeneratorInterface
     */
    protected UrlGeneratorInterface $router;

    /**
     * @var Security
     */
    protected Security $security;

    /**
     * @var RequestStack
     */
    protected RequestStack $requestStack;

    /**
     * @var ParameterBagInterface
     */
    protected ParameterBagInterface $parameterBag;

    /**
     * @var LoggerInterface|mixed
     */
    protected LoggerInterface $logger;


    public function __construct(
        #[AutowireLocator([
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
        ])]
        protected ContainerInterface $handlers
    )
    {
        $this->requestStack = $this->handlers->get('requestStack');
        $this->containerBag = $this->handlers->get('containerBag');
        $this->entityManager = $this->handlers->get('entityManager');
        $this->translator = $this->handlers->get('translator');
        $this->router = $this->handlers->get('router');
        $this->security = $this->handlers->get('security');
        $this->parameterBag = $this->handlers->get('parameterBag');
        $this->logger = $this->handlers->get('logger');
    }

    /** Retourne l'interface UrlGeneratorInterface
     * @return UrlGeneratorInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getRouter(): UrlGeneratorInterface
    {
        return $this->handlers->get('router');
    }

    /**
     * Retourne la class GridService
     * @return GridService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getGridService(): GridService
    {
        return $this->handlers->get('gridService');
    }

    /**
     * Retourne la class OptionSystemService
     * @return OptionSystemService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getOptionSystemService(): OptionSystemService
    {
        return $this->handlers->get('optionSystemService');
    }

    /**
     * Récupère la class RequestStack
     * @return RequestStack
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getRequestStack(): RequestStack
    {
        return $this->handlers->get('requestStack');
    }

    /**
     * Retourne l'interface ContainerBagInterface
     * @return ContainerBagInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getContainerBag(): ContainerBagInterface
    {
        return $this->handlers->get('containerBag');
    }

    /**
     * Retourne l'interface Translator
     * @return TranslatorInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getTranslator(): TranslatorInterface
    {
        return $this->handlers->get('translator');
    }
}
