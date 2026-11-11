<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Version d'une page du sitemap dans une langue
 */

namespace App\Api\V2\Output\Global;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Attribute\Groups;

final readonly class SitemapAlternate
{
    public function __construct(
        #[Groups(['sitemap:list'])] #[ApiProperty(required: true)] public string $locale,
        /**
         * Chemin public de la page (/<locale>/<catégorie>/<slug>)
         */
        #[Groups(['sitemap:list'])] #[ApiProperty(required: true)] public string $path,
    ) {}
}
