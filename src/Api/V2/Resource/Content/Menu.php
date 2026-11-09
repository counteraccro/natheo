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
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\QueryParameter;
use App\Api\V2\Output\Content\MenuElement;
use App\Api\V2\Parameter\LocaleParameter;
use App\Api\V2\Parameter\MenuPositionParameter;
use App\Api\V2\State\Provider\Content\MenuCollectionProvider;
use App\Api\V2\State\Provider\Content\MenuProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[
    ApiResource(
        operations: [
            new Get(
                uriTemplate: '/menus/{id}',
                requirements: ['id' => '\d+'],
                description: 'Un menu actif et ses éléments',
                normalizationContext: ['groups' => ['menu:read']],
                security: "is_granted('ROLE_READ_API')",
                provider: MenuProvider::class,
                parameters: ['locale' => new LocaleParameter()],
            ),
            new GetCollection(
                uriTemplate: '/menus',
                paginationEnabled: false,
                description: 'Les menus affichés sur une page publiée',
                normalizationContext: ['groups' => ['menu:read']],
                security: "is_granted('ROLE_READ_API')",
                provider: MenuCollectionProvider::class,
                parameters: [
                    'locale' => new LocaleParameter(),
                    'page' => new QueryParameter(
                        schema: ['type' => 'string'],
                        description: 'URL (slug) de la page',
                        required: true,
                    ),
                    'position' => new MenuPositionParameter(),
                ],
            ),
        ],
    ),
]
final class Menu
{
    #[ApiProperty(identifier: true)]
    #[Groups(['menu:read', 'page:read'])]
    public int $id = 0;

    /**
     * Position : header, right, footer, left
     */
    #[Groups(['menu:read', 'page:read'])]
    public string $position = '';

    /**
     * Type d'affichage (header_side_bar, footer_colonnes…)
     */
    #[Groups(['menu:read', 'page:read'])]
    public string $type = '';

    /**
     * @var MenuElement[]
     */
    #[ApiProperty(genId: false)]
    #[Groups(['menu:read', 'page:read'])]
    public array $elements = [];
}
