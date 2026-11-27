<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource API v2 : catalogue de traduction de l'interface, messages bruts d'un domaine dans une langue
 */

namespace App\Api\V2\Resource\Admin\System;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use App\Api\V2\Cache\NotModifiedListener;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Metadata\AcceptLanguageParameter;
use App\Api\V2\Metadata\LocaleParameter;
use App\Api\V2\Metadata\OperationSecurityMetadataFactory;
use App\Api\V2\State\Provider\Admin\System\AdminTranslationCatalogueCollectionProvider;
use App\Api\V2\State\Provider\Admin\System\AdminTranslationCatalogueProvider;
use App\Enum\Admin\Module\CoreModuleId;
use Symfony\Component\Serializer\Attribute\Groups;

#[
    ApiResource(
        shortName: 'AdminTranslationCatalogue',
        extraProperties: [
            OperationSecurityMetadataFactory::MODULE => CoreModuleId::DASHBOARD->value,
            OperationSecurityMetadataFactory::LOCKED => true,
        ],
        operations: [
            new GetCollection(
                name: 'admin_translation_catalogue_get_collection',
                uriTemplate: '/admin/translations',
                paginationEnabled: false,
                errors: [],
                openapi: new OpenApiOperation(
                    summary: 'Les domaines de traduction de l\'interface',
                    description: 'Domaines servis par l\'API, rangés par nom, avec l\'IRI du catalogue de chacun.',
                ),
                normalizationContext: ['groups' => ['admin_translation_catalogue:list']],
                provider: AdminTranslationCatalogueCollectionProvider::class,
            ),
            new Get(
                name: 'admin_translation_catalogue_get',
                uriTemplate: '/admin/translations/{domain}',
                requirements: ['domain' => '[a-z0-9_]+'],
                errors: [NotFoundException::class],
                openapi: new OpenApiOperation(
                    summary: 'Le catalogue de traduction d\'un domaine',
                    description: 'Messages bruts au format ICU, modifications du module Traductions comprises. ' .
                        'La réponse porte un ETag : renvoyé dans If-None-Match, il donne un 304 si le catalogue n\'a ' .
                        'pas changé.',
                ),
                normalizationContext: [
                    'groups' => ['admin_translation_catalogue:read'],
                    'preserve_empty_objects' => true,
                ],
                provider: AdminTranslationCatalogueProvider::class,
                parameters: [new LocaleParameter(), new AcceptLanguageParameter()],
                extraProperties: [NotModifiedListener::EXTRA_PROPERTY => true],
            ),
        ],
    ),
]
final class AdminTranslationCatalogue
{
    /**
     * Nom du domaine (nom du fichier de traduction sans suffixe ni langue)
     */
    #[ApiProperty(required: true, identifier: true)]
    #[Groups(['admin_translation_catalogue:read', 'admin_translation_catalogue:list'])]
    public string $domain = '';

    /**
     * Langue des messages
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_translation_catalogue:read'])]
    public string $locale = '';

    /**
     * Messages bruts par clé, au format ICU
     * @var \ArrayObject<string, string>
     */
    #[ApiProperty(required: true, schema: ['type' => 'object', 'additionalProperties' => ['type' => 'string']])]
    #[Groups(['admin_translation_catalogue:read'])]
    public \ArrayObject $messages;

    public function __construct()
    {
        $this->messages = new \ArrayObject();
    }
}
