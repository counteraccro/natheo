<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * En-têtes de dépréciation (Deprecation, Sunset, Link) sur toutes les réponses de l'API v1
 */

namespace App\EventListener;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

#[AsEventListener]
readonly class ApiV1DeprecationListener
{
    public function __construct(
        #[Autowire('%app.api_version%')] private string $apiVersion,
        #[Autowire('%app.api_v1.deprecated_at%')] private string $deprecatedAt,
        #[Autowire('%app.api_v1.sunset_at%')] private ?string $sunsetAt,
        #[Autowire('%app.api_v1.migration_guide_url%')] private string $migrationGuideUrl,
    ) {}

    /**
     * Couvre aussi les erreurs du pare-feu et les routes inconnues sous le préfixe de la v1
     * @param ResponseEvent $event
     * @return void
     */
    public function __invoke(ResponseEvent $event): void
    {
        if (!str_starts_with($event->getRequest()->getPathInfo(), '/api/' . $this->apiVersion . '/')) {
            return;
        }

        $headers = $event->getResponse()->headers;
        $headers->set('Deprecation', '@' . (new \DateTimeImmutable($this->deprecatedAt . ' UTC'))->getTimestamp());
        if ($this->sunsetAt !== null && $this->sunsetAt !== '') {
            $headers->set(
                'Sunset',
                (new \DateTimeImmutable($this->sunsetAt . ' UTC'))->format('D, d M Y H:i:s \G\M\T'),
            );
        }
        $headers->set('Link', '<' . $this->migrationGuideUrl . '>; rel="deprecation"; type="text/html"', false);
    }
}
