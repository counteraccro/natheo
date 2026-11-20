<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource API v2 : module du back-office visible par l'utilisateur de la session
 */

namespace App\Api\V2\Resource\Admin\System;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Metadata\AcceptLanguageParameter;
use App\Api\V2\Metadata\LocaleParameter;
use App\Api\V2\Metadata\OperationSecurityMetadataFactory;
use App\Api\V2\State\Provider\Admin\System\AdminModuleCollectionProvider;
use App\Api\V2\State\Provider\Admin\System\AdminModuleProvider;
use App\Enum\Admin\Module\CoreModuleId;
use Symfony\Component\Serializer\Attribute\Groups;

#[
    ApiResource(
        shortName: 'AdminModule',
        extraProperties: [OperationSecurityMetadataFactory::MODULE => CoreModuleId::DASHBOARD->value],
        operations: [
            new GetCollection(
                name: 'admin_module_get_collection',
                uriTemplate: '/admin/modules',
                paginationEnabled: false,
                errors: [],
                openapi: new OpenApiOperation(
                    summary: 'Les modules du back-office visibles par l\'utilisateur',
                    description: 'Modules rangés dans l\'ordre du menu, avec les opérations d\'API de chacun.',
                ),
                normalizationContext: ['groups' => ['admin_module:list']],
                provider: AdminModuleCollectionProvider::class,
                parameters: [new LocaleParameter(), new AcceptLanguageParameter()],
            ),
            new Get(
                name: 'admin_module_get',
                uriTemplate: '/admin/modules/{id}',
                errors: [NotFoundException::class],
                openapi: new OpenApiOperation(summary: 'Un module du back-office visible par l\'utilisateur'),
                normalizationContext: ['groups' => ['admin_module:read']],
                provider: AdminModuleProvider::class,
                parameters: [new LocaleParameter(), new AcceptLanguageParameter()],
            ),
        ],
    ),
]
final class AdminModule
{
    #[ApiProperty(required: true, identifier: true)]
    #[Groups(['admin_module:read', 'admin_module:list'])]
    public string $id = '';

    /**
     * Nom traduit
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_module:read', 'admin_module:list'])]
    public string $label = '';

    /**
     * Description traduite
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_module:read', 'admin_module:list'])]
    public string $description = '';

    /**
     * Module qui ne peut pas être désactivé
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_module:read', 'admin_module:list'])]
    public bool $locked = false;

    /**
     * Noms des opérations d'API du module
     * @var string[]
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_module:read', 'admin_module:list'])]
    public array $operations = [];
}
