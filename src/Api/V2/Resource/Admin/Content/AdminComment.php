<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource API v2 d'administration : commentaire d'une page
 */

namespace App\Api\V2\Resource\Admin\Content;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use ApiPlatform\Validator\Exception\ValidationException;
use App\Api\V2\Concurrency\VersionedResourceInterface;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Exception\PreconditionFailedException;
use App\Api\V2\Filter\Admin\AdminOrderFilter;
use App\Api\V2\Filter\Admin\AuthorFilter;
use App\Api\V2\Filter\Admin\CommentStatusFilter;
use App\Api\V2\Filter\Admin\PageIdFilter;
use App\Api\V2\Filter\Admin\SearchFilter;
use App\Api\V2\Input\Admin\Content\UpdateCommentInput;
use App\Api\V2\Metadata\AcceptLanguageParameter;
use App\Api\V2\Metadata\EnumSlugPropertyMetadataFactory;
use App\Api\V2\Metadata\LocaleParameter;
use App\Api\V2\Metadata\OperationSecurityMetadataFactory;
use App\Api\V2\Output\Admin\Content\AdminCommentPage;
use App\Api\V2\Output\Admin\Global\AdminAuthor;
use App\Api\V2\Output\Admin\Global\Permissions;
use App\Api\V2\State\Processor\Admin\Content\UpdateAdminCommentProcessor;
use App\Api\V2\State\Provider\Admin\Content\AdminCommentCollectionProvider;
use App\Api\V2\State\Provider\Admin\Content\AdminCommentProvider;
use App\Enum\Admin\Comment\CommentStatus;
use App\Enum\Admin\Module\CoreModuleId;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[
    ApiResource(
        shortName: 'AdminComment',
        extraProperties: [OperationSecurityMetadataFactory::MODULE => CoreModuleId::COMMENT->value],
        normalizationContext: [
            'groups' => ['admin_comment:read', Permissions::GROUP, AdminAuthor::GROUP, AdminCommentPage::GROUP],
        ],
        operations: [
            new GetCollection(
                name: 'admin_comment_get_collection',
                uriTemplate: '/admin/comments',
                errors: [ValidationException::class],
                openapi: new OpenApiOperation(
                    summary: 'Les commentaires',
                    description: 'Extrait du texte, page dans la langue de contenu, filtres par statut de ' .
                        'modération, par page et par modérateur.',
                ),
                normalizationContext: [
                    'groups' => [
                        'admin_comment:list',
                        Permissions::GROUP,
                        AdminAuthor::GROUP,
                        AdminCommentPage::GROUP,
                    ],
                ],
                provider: AdminCommentCollectionProvider::class,
                parameters: [
                    SearchFilter::PARAMETER => new QueryParameter(
                        filter: SearchFilter::class,
                        properties: ['comment', 'author', 'email'],
                        description: 'Recherche dans le texte, l\'auteur et l\'e-mail, sans tenir compte de la casse',
                    ),
                    AdminOrderFilter::PARAMETER => new QueryParameter(
                        filter: AdminOrderFilter::class,
                        properties: ['id', 'author', 'page', 'status', 'createdAt'],
                        description: 'Tri, order[id]=desc par défaut',
                    ),
                    CommentStatusFilter::PARAMETER => new QueryParameter(
                        filter: CommentStatusFilter::class,
                        property: 'status',
                        description: 'Statut de modération',
                    ),
                    PageIdFilter::PARAMETER => new QueryParameter(
                        filter: PageIdFilter::class,
                        property: 'page',
                        description: 'Commentaires d\'une page',
                        constraints: [new Assert\Regex(pattern: '/^[1-9][0-9]*$/')],
                    ),
                    AuthorFilter::PARAMETER => new QueryParameter(
                        filter: AuthorFilter::class,
                        property: 'moderator',
                        description: 'Commentaires modérés par l\'utilisateur de la session',
                    ),
                    new LocaleParameter(),
                    new AcceptLanguageParameter(),
                ],
            ),
            new Get(
                name: 'admin_comment_get',
                uriTemplate: '/admin/comments/{id}',
                requirements: ['id' => '\d+'],
                errors: [NotFoundException::class],
                openapi: new OpenApiOperation(summary: 'Un commentaire'),
                provider: AdminCommentProvider::class,
                parameters: [new LocaleParameter(), new AcceptLanguageParameter()],
            ),
            new Patch(
                name: 'admin_comment_patch',
                uriTemplate: '/admin/comments/{id}',
                requirements: ['id' => '\d+'],
                errors: [NotFoundException::class, PreconditionFailedException::class, ValidationException::class],
                openapi: new OpenApiOperation(
                    summary: 'Modifie ou modère un commentaire',
                    description: 'Un champ absent reste inchangé. Avec le statut moderate, l\'utilisateur de la ' .
                        'session devient le modérateur ; avec un autre statut, le modérateur et la raison sont effacés.',
                ),
                input: UpdateCommentInput::class,
                provider: AdminCommentProvider::class,
                processor: UpdateAdminCommentProcessor::class,
                parameters: [new LocaleParameter(), new AcceptLanguageParameter()],
            ),
        ],
    ),
]
final class AdminComment implements VersionedResourceInterface
{
    #[ApiProperty(required: true, identifier: true)]
    #[Groups(['admin_comment:read', 'admin_comment:list'])]
    public int $id = 0;

    /**
     * Texte en Markdown
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_comment:read'])]
    public string $comment = '';

    /**
     * Texte sans mise en forme, coupé à 100 caractères
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_comment:list'])]
    public string $excerpt = '';

    /**
     * Nom laissé par le visiteur
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_comment:read', 'admin_comment:list'])]
    public string $author = '';

    #[ApiProperty(required: true)]
    #[Groups(['admin_comment:read'])]
    public ?string $email = null;

    #[ApiProperty(required: true)]
    #[Groups(['admin_comment:read'])]
    public string $ip = '';

    #[ApiProperty(required: true)]
    #[Groups(['admin_comment:read'])]
    public string $userAgent = '';

    #[ApiProperty(required: true, extraProperties: [EnumSlugPropertyMetadataFactory::EXTRA_PROPERTY => CommentStatus::class])]
    #[Groups(['admin_comment:read', 'admin_comment:list'])]
    public string $status = '';

    /**
     * Raison de la modération, visible des modérateurs seulement
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_comment:read'])]
    public ?string $moderationComment = null;

    #[ApiProperty(required: true, genId: false)]
    #[Groups(['admin_comment:read', 'admin_comment:list'])]
    public ?AdminAuthor $moderator = null;

    #[ApiProperty(required: true, genId: false)]
    #[Groups(['admin_comment:read', 'admin_comment:list'])]
    public ?AdminCommentPage $page = null;

    #[ApiProperty(required: true)]
    #[Groups(['admin_comment:read', 'admin_comment:list'])]
    public ?string $createdAt = null;

    #[ApiProperty(required: true)]
    #[Groups(['admin_comment:read', 'admin_comment:list'])]
    public ?string $updatedAt = null;

    #[ApiProperty(required: true, genId: false)]
    #[Groups(['admin_comment:read', 'admin_comment:list'])]
    public ?Permissions $permissions = null;

    /**
     * Empreinte de la date et du contenu, la date à la seconde ne distinguant pas deux écritures
     * @return string|null
     */
    public function getVersion(): ?string
    {
        return json_encode(
            [$this->updatedAt, $this->comment, $this->status, $this->moderationComment, $this->moderator?->id],
            JSON_THROW_ON_ERROR,
        );
    }
}
