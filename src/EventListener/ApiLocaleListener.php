<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Langue des requêtes de l'API v2 : résolue une fois depuis « locale » et Accept-Language, appliquée au
 * traducteur (contenus et erreurs), renvoyée dans Content-Language
 */

namespace App\EventListener;

use ApiPlatform\Metadata\HttpOperation;
use App\Service\Api\ApiService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Translation\LocaleSwitcher;

readonly class ApiLocaleListener
{
    /**
     * Attribut de requête portant la langue résolue
     * @var string
     */
    public const string ATTRIBUTE = '_api_content_locale';

    public function __construct(private ApiService $apiService, private LocaleSwitcher $localeSwitcher) {}

    /**
     * Après le routeur (32), avant le pare-feu (8) pour traduire aussi les erreurs d'authentification
     * @param RequestEvent $event
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[AsEventListener(event: KernelEvents::REQUEST, priority: 20)]
    public function onRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$request->attributes->has('_api_resource_class')) {
            return;
        }

        $locale = $request->query->get('locale');
        $locale = $this->apiService->resolveLocale(
            is_string($locale) ? $locale : null,
            $request->headers->get('Accept-Language'),
        );
        $request->attributes->set(self::ATTRIBUTE, $locale);
        $request->setLocale($locale);
        $this->localeSwitcher->setLocale($locale);
    }

    /**
     * @param ResponseEvent $event
     * @return void
     */
    #[AsEventListener(event: KernelEvents::RESPONSE)]
    public function onResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $locale = $request->attributes->get(self::ATTRIBUTE);
        if (!$event->isMainRequest() || !is_string($locale)) {
            return;
        }

        $operation = $request->attributes->get('_api_operation');
        $response = $event->getResponse();
        $isContent = $operation instanceof HttpOperation && $operation->getParameters()?->get('locale') !== null;

        // Les erreurs sont traduites dans la langue demandée, quelle que soit la ressource
        if ($isContent || !$response->isSuccessful()) {
            $response->setVary('Accept-Language', false);
        }
        if ($isContent && $response->isSuccessful()) {
            $response->headers->set('Content-Language', $locale);
        }
    }
}
