<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Handler pour les API Controller
 */
namespace App\Controller\Api\v1;

use App\Service\Admin\System\User\UserDataService;
use App\Service\Admin\System\User\UserService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Contracts\Translation\TranslatorInterface;

class AppApiHandlerController extends AbstractController
{
    public function __construct(
        #[AutowireLocator([
            'translator' => TranslatorInterface::class,
            'userService' => UserService::class,
            'userDataService' => UserDataService::class,
        ])]
        protected ContainerInterface $handlers
    ){}

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
     * Retourne un UserService
     * @return UserService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getUserService(): UserService
    {
        return $this->handlers->get('userService');
    }

    /**
     * Retourne un userDataService
     * @return UserDataService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getUserDataService(): UserDataService
    {
        return $this->handlers->get('userDataService');
    }
}
