<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Handler pour les API Controller
 */
namespace App\Controller\Api\v1;

use App\Service\Admin\System\User\UserDataService;
use App\Service\Admin\System\User\UserService;
use App\Service\Api\Content\ApiCommentService;
use App\Service\Api\Content\ApiMenuService;
use App\Service\Api\Content\Page\ApiPageContentService;
use App\Service\Api\Content\Page\ApiPageService;
use App\Service\Api\System\User\ApiUserService;
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
            'apiUserService' => ApiUserService::class,
            'userDataService' => UserDataService::class,
            'apiMenuService' => ApiMenuService::class,
            'apiPageService' => ApiPageService::class,
            'apiPageContentService' => ApiPageContentService::class,
            'apiCommentService' => ApiCommentService::class,
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

    /**
     * Retourne un apiUserService
     * @return ApiUserService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getApiUserService(): ApiUserService
    {
        return $this->handlers->get('apiUserService');
    }

    /**
     * Retourne un apiMenuService
     * @return ApiMenuService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getApiMenuService(): ApiMenuService
    {
        return $this->handlers->get('apiMenuService');
    }

    /**
     * Retourne un ApiPageService
     * @return ApiPageService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getApiPageService(): ApiPageService
    {
        return $this->handlers->get('apiPageService');
    }

    /**
     * Retourne un ApiPageContentService
     * @return ApiPageContentService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getApiPageContentService() : ApiPageContentService
    {
        return $this->handlers->get('apiPageContentService');
    }

    /**
     * Retourne un ApiCommentService
     * @return ApiCommentService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getApiCommentService(): ApiCommentService
    {
        return $this->handlers->get('apiCommentService');
    }
}
