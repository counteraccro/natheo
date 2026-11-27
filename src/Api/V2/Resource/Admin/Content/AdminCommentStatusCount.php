<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource API v2 d'administration : nombre de commentaires d'un statut de modération
 */

namespace App\Api\V2\Resource\Admin\Content;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Metadata\EnumSlugPropertyMetadataFactory;
use App\Api\V2\Metadata\OperationSecurityMetadataFactory;
use App\Api\V2\State\Provider\Admin\Content\AdminCommentStatusCountProvider;
use App\Enum\Admin\Comment\CommentStatus;
use App\Enum\Admin\Module\CoreModuleId;
use Symfony\Component\Serializer\Attribute\Groups;

#[
    ApiResource(
        shortName: 'AdminCommentStatusCount',
        extraProperties: [OperationSecurityMetadataFactory::MODULE => CoreModuleId::COMMENT->value],
        normalizationContext: ['groups' => ['admin_comment_status_count:read']],
        provider: AdminCommentStatusCountProvider::class,
        operations: [
            new GetCollection(
                name: 'admin_comment_status_count_get_collection',
                uriTemplate: '/admin/comment_status_counts',
                paginationEnabled: false,
                errors: [],
                openapi: new OpenApiOperation(
                    summary: 'Nombre de commentaires par statut de modération',
                    description: 'Un élément par statut, dans l\'ordre des statuts, nombre à 0 compris.',
                ),
            ),
            new Get(
                name: 'admin_comment_status_count_get',
                uriTemplate: '/admin/comment_status_counts/{status}',
                errors: [NotFoundException::class],
                openapi: new OpenApiOperation(summary: 'Nombre de commentaires d\'un statut de modération'),
            ),
        ],
    ),
]
final class AdminCommentStatusCount
{
    /**
     * @param string $status
     * @param int $count
     */
    public function __construct(
        #[ApiProperty(required: true, identifier: true, extraProperties: [EnumSlugPropertyMetadataFactory::EXTRA_PROPERTY => CommentStatus::class])]
        #[Groups(['admin_comment_status_count:read'])]
        public string $status = '',
        #[ApiProperty(required: true)]
        #[Groups(['admin_comment_status_count:read'])]
        public int $count = 0,
    ) {}
}
