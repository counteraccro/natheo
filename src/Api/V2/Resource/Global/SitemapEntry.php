<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource API v2 : page publiée du sitemap avec ses versions par langue
 */

namespace App\Api\V2\Resource\Global;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use App\Api\V2\Output\Global\SitemapAlternate;
use App\Api\V2\Resource\Content\Page;
use App\Api\V2\State\Provider\Global\SitemapProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[
    ApiResource(
        operations: [
            new GetCollection(
                uriTemplate: '/sitemap',
                errors: [],
                openapi: new OpenApiOperation(
                    summary: 'Les pages publiées du sitemap, les plus récemment modifiées en premier',
                    description: 'Une entrée par page, avec le chemin public de chacune de ses langues (hreflang).',
                ),
                normalizationContext: ['groups' => ['sitemap_entry:list']],
                security: "is_granted('ROLE_READ_API')",
                provider: SitemapProvider::class,
            ),
        ],
    ),
]
final class SitemapEntry
{
    #[ApiProperty(required: true)]
    #[Groups(['sitemap_entry:list'])]
    public ?Page $page = null;

    #[ApiProperty(required: true)]
    #[Groups(['sitemap_entry:list'])]
    public ?\DateTimeInterface $updatedAt = null;

    /**
     * @var SitemapAlternate[]
     */
    #[ApiProperty(genId: false, required: true)]
    #[Groups(['sitemap_entry:list'])]
    public array $alternates = [];
}
