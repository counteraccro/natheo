<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource API v2 : commentaire d'une page publiée
 */

namespace App\Api\V2\Resource\Content;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use ApiPlatform\Validator\Exception\ValidationException;
use App\Api\V2\Exception\ForbiddenException;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Exception\TooManyRequestsException;
use App\Api\V2\Filter\OrderFilter;
use App\Api\V2\Input\Content\CreateCommentInput;
use App\Api\V2\Input\Content\ModerateCommentInput;
use App\Api\V2\Metadata\EnumSlugPropertyMetadataFactory;
use App\Api\V2\State\Processor\Content\CreateCommentProcessor;
use App\Api\V2\State\Processor\Content\ModerateCommentProcessor;
use App\Api\V2\State\Provider\Content\CommentCollectionProvider;
use App\Api\V2\State\Provider\Content\CommentProvider;
use App\Enum\Admin\Comment\CommentStatus;
use Symfony\Component\Serializer\Attribute\Groups;

#[
    ApiResource(
        operations: [
            new Get(
                uriTemplate: '/comments/{id}',
                requirements: ['id' => '\d+'],
                errors: [NotFoundException::class, ForbiddenException::class],
                openapi: new OpenApiOperation(summary: 'Un commentaire actif d\'une page publiée'),
                normalizationContext: ['groups' => ['comment:read']],
                security: "is_granted('ROLE_READ_API')",
                provider: CommentProvider::class,
            ),
            new GetCollection(
                uriTemplate: '/pages/{pageId}/comments',
                uriVariables: ['pageId' => new Link(fromClass: Page::class)],
                requirements: ['pageId' => '\d+'],
                errors: [NotFoundException::class, ForbiddenException::class, ValidationException::class],
                openapi: new OpenApiOperation(
                    summary: 'Les commentaires actifs d\'une page publiée',
                    description: 'Sans utilisateur autorisé à modérer, le texte des commentaires en attente ' .
                        'ou modérés et l\'auteur des commentaires en attente valent null.',
                ),
                normalizationContext: ['groups' => ['comment:list']],
                security: "is_granted('ROLE_READ_API')",
                provider: CommentCollectionProvider::class,
                parameters: [
                    OrderFilter::PARAMETER => new QueryParameter(
                        filter: OrderFilter::class,
                        property: 'createdAt',
                        description: 'Tri par date de création',
                    ),
                ],
            ),
            new Post(
                uriTemplate: '/comments',
                errors: [
                    NotFoundException::class,
                    ForbiddenException::class,
                    TooManyRequestsException::class,
                    ValidationException::class,
                ],
                openapi: new OpenApiOperation(
                    summary: 'Ajoute un commentaire à une page publiée',
                    description: 'En attente de validation si la page ou les options du site l\'exigent.',
                ),
                normalizationContext: ['groups' => ['comment:read']],
                security: "is_granted('ROLE_WRITE_API')",
                input: CreateCommentInput::class,
                processor: CreateCommentProcessor::class,
            ),
            new Patch(
                uriTemplate: '/comments/{id}',
                requirements: ['id' => '\d+'],
                errors: [NotFoundException::class, ForbiddenException::class, ValidationException::class],
                openapi: new OpenApiOperation(
                    summary: 'Modère un commentaire',
                    description: 'Réservé à un utilisateur autorisé à modérer, authentifié par l\'access token de sa session. ' .
                        'Un champ absent ou null reste inchangé ; une chaîne vide efface le message de modération.',
                ),
                normalizationContext: ['groups' => ['comment:read']],
                security: "is_granted('ROLE_WRITE_API')",
                input: ModerateCommentInput::class,
                read: false,
                processor: ModerateCommentProcessor::class,
            ),
        ],
    ),
]
final class Comment
{
    #[ApiProperty(required: true, identifier: true)]
    #[Groups(['comment:read', 'comment:list'])]
    public int $id = 0;

    #[ApiProperty(required: true)]
    #[Groups(['comment:read', 'comment:list'])]
    public ?Page $page = null;

    #[
        ApiProperty(
            required: true,
            extraProperties: [EnumSlugPropertyMetadataFactory::EXTRA_PROPERTY => CommentStatus::class],
        ),
    ]
    #[Groups(['comment:read', 'comment:list'])]
    public string $status = '';

    #[ApiProperty(required: true)]
    #[Groups(['comment:read', 'comment:list'])]
    public ?string $author = null;

    /**
     * Texte en Markdown brut, à assainir avant affichage
     */
    #[ApiProperty(required: true)]
    #[Groups(['comment:read', 'comment:list'])]
    public ?string $comment = null;

    /**
     * Message de modération, renvoyé seulement à un utilisateur autorisé à modérer
     */
    #[ApiProperty(required: true)]
    #[Groups(['comment:read', 'comment:list'])]
    public ?string $moderationComment = null;

    #[ApiProperty(required: true)]
    #[Groups(['comment:read', 'comment:list'])]
    public ?\DateTimeInterface $createdAt = null;

    #[ApiProperty(required: true)]
    #[Groups(['comment:read', 'comment:list'])]
    public ?\DateTimeInterface $updatedAt = null;
}
