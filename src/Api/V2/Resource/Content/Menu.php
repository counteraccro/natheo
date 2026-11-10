<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource API v2 : menu actif
 */

namespace App\Api\V2\Resource\Content;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\HeaderParameter;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use ApiPlatform\Validator\Exception\ValidationException;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Output\Content\MenuElement;
use App\Api\V2\Filter\LocaleFilter;
use App\Api\V2\Filter\MenuPositionFilter;
use App\Api\V2\State\Provider\Content\MenuCollectionProvider;
use App\Api\V2\State\Provider\Content\MenuProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[
    ApiResource(
        operations: [
            new Get(
                uriTemplate: '/menus/{id}',
                errors: [NotFoundException::class, ValidationException::class],
                requirements: ['id' => '\d+'],
                openapi: new OpenApiOperation(summary: 'Un menu actif et ses éléments'),
                normalizationContext: ['groups' => ['menu:read']],
                security: "is_granted('ROLE_READ_API')",
                provider: MenuProvider::class,
                parameters: [
                    'locale' => new QueryParameter(filter: LocaleFilter::class, property: 'locale', description: LocaleFilter::LOCALE_DESCRIPTION),
                    LocaleFilter::HEADER => new HeaderParameter(schema: ['type' => 'string'], description: LocaleFilter::HEADER_DESCRIPTION),
                ],
            ),
            new GetCollection(
                uriTemplate: '/menus',
                errors: [NotFoundException::class, ValidationException::class],
                paginationEnabled: false,
                openapi: new OpenApiOperation(summary: 'Les menus affichés sur une page publiée'),
                normalizationContext: ['groups' => ['menu:read']],
                security: "is_granted('ROLE_READ_API')",
                provider: MenuCollectionProvider::class,
                parameters: [
                    'locale' => new QueryParameter(filter: LocaleFilter::class, property: 'locale', description: LocaleFilter::LOCALE_DESCRIPTION),
                    LocaleFilter::HEADER => new HeaderParameter(schema: ['type' => 'string'], description: LocaleFilter::HEADER_DESCRIPTION),
                    'page' => new QueryParameter(
                        schema: ['type' => 'string'],
                        property: 'page',
                        description: 'URL (slug) de la page',
                        required: true,
                    ),
                    'position' => new QueryParameter(filter: MenuPositionFilter::class, description: 'Position du menu'),
                ],
            ),
        ],
    ),
]
final class Menu
{
    #[ApiProperty(identifier: true, required: true)]
    #[Groups(['menu:read', 'page:read'])]
    public int $id = 0;

    /**
     * Position : header, right, footer, left
     */
    #[ApiProperty(required: true)]
    #[Groups(['menu:read', 'page:read'])]
    public string $position = '';

    /**
     * Type d'affichage (header_side_bar, footer_colonnes…)
     */
    #[ApiProperty(required: true)]
    #[Groups(['menu:read', 'page:read'])]
    public string $type = '';

    /**
     * @var MenuElement[]
     */
    #[ApiProperty(genId: false, required: true)]
    #[Groups(['menu:read', 'page:read'])]
    public array $elements = [];
}
