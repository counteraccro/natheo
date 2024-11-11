<?php
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Class qui charge les class nécessaire en autowrite pour l'API front
 */
namespace App\Service\Api;


use App\Service\Admin\Content\Page\PageService;
use App\Service\Admin\System\OptionSystemService;
use App\Service\Api\Content\ApiMenuService;
use App\Service\Api\Content\Page\ApiPageContentService;
use App\Utils\Translate\GridTranslate;
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
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AppApiHandlerService
{

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
            'pageService' => PageService::class,
            'apiMenuService' => ApiMenuService::class,
            'apiPageContentService' => ApiPageContentService::class,
            'kernel' => KernelInterface::class,
        ])]
        protected ContainerInterface $handlers){}

    /**
     * Retourne l'interface LoggerInterface
     * @return LoggerInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getLogger(): LoggerInterface
    {
        return $this->handlers->get('logger');
    }

    /**
     * Retourne l'interface EntityManagerInterface
     * @return EntityManagerInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->handlers->get('entityManager');
    }

    /**
     * Retourne la class GridTranslate
     * @return GridTranslate
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getGridTranslate() : GridTranslate
    {
        return $this->handlers->get('gridTranslate');
    }

    /**
     * Retourne l'interface KernelInterface
     * @return KernelInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getKernel() : KernelInterface
    {
        return $this->handlers->get('kernel');
    }


    /**
     * Retourne l'interface ParameterBagInterface
     * @return ParameterBagInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getParameterBag(): ParameterBagInterface
    {
        return $this->handlers->get('parameterBag');
    }


    /**
     * Retourne la class Security
     * @return Security
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getSecurity(): Security
    {
        return $this->handlers->get('security');
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

    /**
     * Retourne PageService
     * @return PageService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getPageService() : PageService
    {
        return $this->handlers->get('pageService');
    }

    /**
     * Retourne ApiMenuService
     * @return ApiMenuService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getApiMenuService(): ApiMenuService
    {
        return $this->handlers->get('apiMenuService');
    }

    /**
     * Retourne ApiPageContentService
     * @return ApiPageContentService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getApiPageContentService(): ApiPageContentService
    {
        return $this->handlers->get('apiPageContentService');
    }
}
