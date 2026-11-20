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
use ApiPlatform\Validator\Exception\ValidationException;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Metadata\AcceptLanguageParameter;
use App\Api\V2\Metadata\LocaleParameter;
use App\Api\V2\State\Provider\System\OptionSystemCollectionProvider;
use App\Api\V2\State\Provider\System\OptionSystemProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[
    ApiResource(
        operations: [
            new Get(
                name: 'option_system_get',
                uriTemplate: '/option_systems/{key}',
                errors: [NotFoundException::class, ValidationException::class],
                openapi: new OpenApiOperation(summary: 'Une option système publique par sa clé'),
                normalizationContext: ['groups' => ['option_system:read']],
                security: "is_granted('ROLE_READ_API')",
                provider: OptionSystemProvider::class,
                parameters: [new LocaleParameter(), new AcceptLanguageParameter()],
            ),
            new GetCollection(
                name: 'option_system_get_collection',
                uriTemplate: '/option_systems',
                paginationEnabled: false,
                errors: [ValidationException::class],
                openapi: new OpenApiOperation(summary: 'Les options système publiques'),
                normalizationContext: ['groups' => ['option_system:list']],
                security: "is_granted('ROLE_READ_API')",
                provider: OptionSystemCollectionProvider::class,
                parameters: [new LocaleParameter(), new AcceptLanguageParameter()],
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
         * Booléen pour les options oui / non, chaîne sinon ; texte dans la langue demandée pour une option traduisible
         */
        #[ApiProperty(required: true)] #[
            Groups(['option_system:read', 'option_system:list']),
        ]
        public bool|string|null $value,
    ) {}
}
