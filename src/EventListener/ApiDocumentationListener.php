<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Masque la documentation publique de l'API v2 quand l'API est fermée
 */

namespace App\EventListener;

use App\Service\Api\ApiService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::REQUEST, priority: 16)]
readonly class ApiDocumentationListener
{
    /**
     * Routes API Platform accessibles sans token
     * @var string[]
     */
    private const array DOCUMENTATION_ROUTES = ['api_doc', 'api_entrypoint', 'api_jsonld_context'];

    public function __construct(private ApiService $apiService) {}

    /**
     * @param RequestEvent $event
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $route = $event->getRequest()->attributes->get('_route');
        if (in_array($route, self::DOCUMENTATION_ROUTES, true) && !$this->apiService->isApiOpen()) {
            throw new NotFoundHttpException();
        }
    }
}
