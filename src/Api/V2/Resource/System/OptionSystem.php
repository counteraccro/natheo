<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource API v2 : option système publique
 */

namespace App\Api\V2\Resource\System;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\State\Provider\System\OptionSystemCollectionProvider;
use App\Api\V2\State\Provider\System\OptionSystemProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[
    ApiResource(
        operations: [
            new Get(
                uriTemplate: '/option_systems/{key}',
                errors: [NotFoundException::class],
                openapi: new OpenApiOperation(summary: 'Une option système publique par sa clé'),
                normalizationContext: ['groups' => ['option_system:read']],
                security: "is_granted('ROLE_READ_API')",
                provider: OptionSystemProvider::class,
            ),
            new GetCollection(
                uriTemplate: '/option_systems',
                paginationEnabled: false,
                errors: [],
                openapi: new OpenApiOperation(summary: 'Les options système publiques'),
                normalizationContext: ['groups' => ['option_system:list']],
                security: "is_granted('ROLE_READ_API')",
                provider: OptionSystemCollectionProvider::class,
            ),
        ],
    ),
]
final readonly class OptionSystem
{
    public function __construct(
        /**
         * Clé en minuscules (site_name, open_comment…)
         */
        #[ApiProperty(required: true, identifier: true)] #[
            Groups(['option_system:read', 'option_system:list']),
        ]
        public string $key,
        /**
         * Booléen pour les options oui / non, chaîne sinon
         */
        #[ApiProperty(required: true)] #[
            Groups(['option_system:read', 'option_system:list']),
        ]
        public bool|string|null $value,
    ) {}
}
