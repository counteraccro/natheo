<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Opérations de lecture revalidées à chaque appel : cache privé, ETag, et 304 sans corps quand l'ETag correspond à
 * l'en-tête If-None-Match
 */

namespace App\Api\V2\Cache;

use ApiPlatform\Metadata\HttpOperation;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::RESPONSE, priority: -10)]
final class NotModifiedListener
{
    /**
     * Clé de l'extraProperties qui active la revalidation sur une opération de lecture
     * @var string
     */
    public const string EXTRA_PROPERTY = 'not_modified';

    /**
     * @param ResponseEvent $event
     * @return void
     */
    public function __invoke(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();
        $operation = $request->attributes->get('_api_operation');
        if (
            !$event->isMainRequest() ||
            !($operation instanceof HttpOperation) ||
            ($operation->getExtraProperties()[self::EXTRA_PROPERTY] ?? false) !== true ||
            !$request->isMethodCacheable() ||
            $response->getStatusCode() !== 200
        ) {
            return;
        }

        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache');
        if ($response->getEtag() === null) {
            $response->setEtag(hash('xxh3', (string) $response->getContent()));
        }
        $response->isNotModified($request);
    }
}
