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
use ApiPlatform\Metadata\HeaderParameter;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\OpenApi\Model\Parameter as OpenApiParameter;
use ApiPlatform\Validator\Exception\ValidationException;
use App\Api\V2\Exception\ForbiddenException;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Exception\TooManyRequestsException;
use App\Api\V2\Input\Content\CommentInput;
use App\Api\V2\Input\Content\CommentModerationInput;
use App\Api\V2\Security\UserTokenResolver;
use App\Api\V2\State\Processor\Content\CreateCommentProcessor;
use App\Api\V2\State\Processor\Content\ModerateCommentProcessor;
use App\Api\V2\State\Provider\Content\CommentCollectionProvider;
use App\Api\V2\State\Provider\Content\CommentProvider;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

// Obligatoire dans la documentation seulement : son absence renvoie 403 et non 422
#[
    ApiResource(
        operations: [
            new Get(
                uriTemplate: '/comments/{id}',
                requirements: ['id' => '\d+'],
                errors: [NotFoundException::class, ForbiddenException::class],
                description: 'Un commentaire actif d\'une page publiée',
                normalizationContext: ['groups' => ['comment:read']],
                security: "is_granted('ROLE_READ_API')",
                provider: CommentProvider::class,
                parameters: [
                    UserTokenResolver::HEADER => new HeaderParameter(
                        schema: ['type' => 'string'],
                        description: UserTokenResolver::HEADER_DESCRIPTION,
                    ),
                ],
            ),
            new GetCollection(
                uriTemplate: '/pages/{pageId}/comments',
                uriVariables: ['pageId' => new Link(fromClass: Page::class)],
                requirements: ['pageId' => '\d+'],
                errors: [NotFoundException::class, ForbiddenException::class, ValidationException::class],
                description: 'Les commentaires actifs d\'une page publiée. Sans utilisateur autorisé à modérer, ' .
                    'le texte des commentaires en attente ou modérés et l\'auteur des commentaires en attente valent null',
                normalizationContext: ['groups' => ['comment:read']],
                security: "is_granted('ROLE_READ_API')",
                provider: CommentCollectionProvider::class,
                parameters: [
                    'order' => new QueryParameter(
                        schema: ['type' => 'string', 'enum' => ['asc', 'desc'], 'default' => 'desc'],
                        description: 'Tri par date de création',
                        constraints: [new Assert\Choice(choices: ['asc', 'desc'])],
                    ),
                    UserTokenResolver::HEADER => new HeaderParameter(
                        schema: ['type' => 'string'],
                        description: UserTokenResolver::HEADER_DESCRIPTION,
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
                description: 'Ajoute un commentaire à une page publiée, en attente de validation si la page ' .
                    'ou les options du site l\'exigent',
                normalizationContext: ['groups' => ['comment:read']],
                security: "is_granted('ROLE_WRITE_API')",
                input: CommentInput::class,
                processor: CreateCommentProcessor::class,
            ),
            new Patch(
                uriTemplate: '/comments/{id}',
                requirements: ['id' => '\d+'],
                errors: [NotFoundException::class, ForbiddenException::class, ValidationException::class],
                description: 'Modère un commentaire, réservé à un utilisateur autorisé à modérer (en-tête X-User-Token)',
                normalizationContext: ['groups' => ['comment:read']],
                security: "is_granted('ROLE_WRITE_API')",
                input: CommentModerationInput::class,
                provider: CommentProvider::class,
                processor: ModerateCommentProcessor::class,
                parameters: [
                    UserTokenResolver::HEADER => new HeaderParameter(
                        schema: ['type' => 'string'],
                        openApi: new OpenApiParameter(name: UserTokenResolver::HEADER, in: 'header', required: true),
                        description: UserTokenResolver::HEADER_DESCRIPTION,
                    ),
                ],
            ),
        ],
    ),
]
final class Comment
{
    #[ApiProperty(required: true, identifier: true)]
    #[Groups(['comment:read'])]
    public int $id = 0;

    #[ApiProperty(required: true)]
    #[Groups(['comment:read'])]
    public ?Page $page = null;

    /**
     * Statut : wait_validation, validate ou moderate
     */
    #[ApiProperty(required: true)]
    #[Groups(['comment:read'])]
    public string $status = '';

    #[ApiProperty(required: true)]
    #[Groups(['comment:read'])]
    public ?string $author = null;

    /**
     * Texte en Markdown brut, à assainir avant affichage
     */
    #[ApiProperty(required: true)]
    #[Groups(['comment:read'])]
    public ?string $comment = null;

    /**
     * Message de modération, renvoyé seulement à un utilisateur autorisé à modérer
     */
    #[ApiProperty(required: true)]
    #[Groups(['comment:read'])]
    public ?string $moderationComment = null;

    #[ApiProperty(required: true)]
    #[Groups(['comment:read'])]
    public ?\DateTimeInterface $createdAt = null;

    #[ApiProperty(required: true)]
    #[Groups(['comment:read'])]
    public ?\DateTimeInterface $updatedAt = null;
}
