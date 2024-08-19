<?php

namespace App\Service\Admin;

use App\Service\Admin\System\OptionSystemService;
use App\Utils\Global\DataBase;
use App\Utils\Global\EnvFile;
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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AppAdminHandlerService
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
            'gridService' => GridService::class,
            'markdownEditorService' => MarkdownEditorService::class,
            'userPasswordHasher' => UserPasswordHasherInterface::class,
            'mailer' => MailerInterface::class,
            'kernel' => KernelInterface::class,
            'gridTranslate' => GridTranslate::class,
            'database' => Database::class,
            'envFile' => EnvFile::class
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
     * Retourne l'interface MailerInterface
     * @return MailerInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getMailer(): MailerInterface
    {
        return $this->handlers->get('mailer');
    }

    /**
     * Retourne l'interface UserPasswordHasherInterface
     * @return UserPasswordHasherInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getUserPasswordHasher(): UserPasswordHasherInterface
    {
        return $this->handlers->get('userPasswordHasher');
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
     * Retourne la class MarkdownEditorService
     * @return MarkdownEditorService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getMarkdownEditorService(): MarkdownEditorService
    {
        return $this->handlers->get('markdownEditorService');
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

    /**
     * Retourne le EnvFile
     * @return EnvFile
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getEnvFile(): EnvFile
    {
        return $this->handlers->get('envFile');
    }
}
