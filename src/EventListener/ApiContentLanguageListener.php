<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ajoute l'en-tête Content-Language aux réponses des ressources de contenu de l'API v2
 */

namespace App\EventListener;

use ApiPlatform\Metadata\HttpOperation;
use App\Api\V2\Filter\LocaleFilter;
use App\Service\Api\ApiService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::RESPONSE)]
readonly class ApiContentLanguageListener
{
    public function __construct(private ApiService $apiService) {}

    /**
     * @param ResponseEvent $event
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ResponseEvent $event): void
    {
        $operation = $event->getRequest()->attributes->get('_api_operation');
        $response = $event->getResponse();
        if (
            !$event->isMainRequest() ||
            !$operation instanceof HttpOperation ||
            $operation->getParameters()?->get('locale') === null ||
            !$response->isSuccessful()
        ) {
            return;
        }

        $response->headers->set('Content-Language', $this->apiService->resolveLocale(...LocaleFilter::getValues($operation)));
    }
}
