<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Ressource API v2 : informations générales de l'installation
 */

namespace App\Api\V2\Resource\System;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use App\Api\V2\State\Provider\System\InfoProvider;
use App\Api\V2\Metadata\OperationSecurityMetadataFactory;

#[
    ApiResource(
        shortName: 'Info',
        operations: [
            new Get(
                name: 'info_get',
                extraProperties: [OperationSecurityMetadataFactory::SHARED => true],
                uriTemplate: '/info',
                errors: [],
                openapi: new OpenApiOperation(summary: 'Nom du site et versions de Nathéo et de l\'API'),
                security: "is_granted('ROLE_READ_API')",
                provider: InfoProvider::class,
            ),
        ],
    ),
]
final readonly class Info
{
    public function __construct(
        #[ApiProperty(required: true)] public string $siteName,
        #[ApiProperty(required: true)] public string $natheoVersion,
        #[ApiProperty(required: true)] public string $apiVersion,
        #[
            ApiProperty(
                description: 'Données ajoutées par une surcharge du provider (config/cms/overwrite.yaml), null sans surcharge',
                required: true,
                schema: ['type' => ['object', 'null'], 'additionalProperties' => true],
            ),
        ]
        public ?array $extra = null,
    ) {}
}
