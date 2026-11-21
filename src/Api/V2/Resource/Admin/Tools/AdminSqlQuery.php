<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource API v2 d'administration : requête enregistrée dans le gestionnaire SQL
 */

namespace App\Api\V2\Resource\Admin\Tools;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use ApiPlatform\Validator\Exception\ValidationException;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Input\Admin\Tools\CreateSqlQueryInput;
use App\Api\V2\Metadata\OperationSecurityMetadataFactory;
use App\Api\V2\State\Processor\Admin\Tools\CreateAdminSqlQueryProcessor;
use App\Api\V2\State\Provider\Admin\Tools\AdminSqlQueryProvider;
use App\Enum\Admin\Module\CoreModuleId;
use Symfony\Component\Serializer\Attribute\Groups;

#[
    ApiResource(
        shortName: 'AdminSqlQuery',
        extraProperties: [OperationSecurityMetadataFactory::MODULE => CoreModuleId::SQL_MANAGER->value],
        normalizationContext: ['groups' => ['admin_sql_query:read']],
        operations: [
            new Get(
                name: 'admin_sql_query_get',
                uriTemplate: '/admin/sql_queries/{id}',
                requirements: ['id' => '\d+'],
                errors: [NotFoundException::class],
                openapi: new OpenApiOperation(summary: 'Une requête du gestionnaire SQL'),
                provider: AdminSqlQueryProvider::class,
            ),
            new Post(
                name: 'admin_sql_query_post',
                uriTemplate: '/admin/sql_queries',
                errors: [ValidationException::class],
                openapi: new OpenApiOperation(
                    summary: 'Enregistre une requête dans le gestionnaire SQL',
                    description: 'Sans nom, la requête reçoit un nom par défaut.',
                ),
                input: CreateSqlQueryInput::class,
                processor: CreateAdminSqlQueryProcessor::class,
            ),
        ],
    ),
]
final class AdminSqlQuery
{
    #[ApiProperty(required: true, identifier: true)]
    #[Groups(['admin_sql_query:read'])]
    public int $id = 0;

    #[ApiProperty(required: true)]
    #[Groups(['admin_sql_query:read'])]
    public string $name = '';

    #[ApiProperty(required: true)]
    #[Groups(['admin_sql_query:read'])]
    public string $query = '';

    #[ApiProperty(required: true)]
    #[Groups(['admin_sql_query:read'])]
    public bool $disabled = false;

    #[ApiProperty(required: true)]
    #[Groups(['admin_sql_query:read'])]
    public ?string $createdAt = null;

    #[ApiProperty(required: true)]
    #[Groups(['admin_sql_query:read'])]
    public ?string $updatedAt = null;
}
