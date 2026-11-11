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

#[
    ApiResource(
        operations: [
            new Get(
                uriTemplate: '/options_systems/{key}',
                errors: [NotFoundException::class],
                openapi: new OpenApiOperation(summary: 'Une option système publique par sa clé'),
                security: "is_granted('ROLE_READ_API')",
                provider: OptionSystemProvider::class,
            ),
            new GetCollection(
                uriTemplate: '/options_systems',
                paginationEnabled: false,
                errors: [],
                openapi: new OpenApiOperation(summary: 'Les options système publiques'),
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
        #[ApiProperty(required: true, identifier: true)] public string $key,
        /**
         * Booléen pour les options oui / non, chaîne sinon
         */
        #[ApiProperty(required: true)] public bool|string|null $value,
    ) {}
}
