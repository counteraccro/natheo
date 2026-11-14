<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Langues du site, lues dans le paramètre app.supported_locales
 */

namespace App\Service\Global;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class SupportedLocales
{
    /**
     * @var string[]
     */
    private array $locales;

    /**
     * @param string $supportedLocales langues séparées par « | »
     */
    public function __construct(#[Autowire('%app.supported_locales%')] string $supportedLocales)
    {
        $this->locales = explode('|', $supportedLocales);
    }

    /**
     * Retourne les langues du site
     * @return string[]
     */
    public function getAll(): array
    {
        return $this->locales;
    }
}
