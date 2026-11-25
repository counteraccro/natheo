<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource API v2 d'administration : FAQ, avec ses catégories et le titre de ses questions
 */

namespace App\Api\V2\Resource\Admin\Content;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use ApiPlatform\Validator\Exception\ValidationException;
use App\Api\V2\Concurrency\VersionedResourceInterface;
use App\Api\V2\Exception\ConflictException;
use App\Api\V2\Exception\ForbiddenException;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Exception\PreconditionFailedException;
use App\Api\V2\Filter\Admin\AdminOrderFilter;
use App\Api\V2\Filter\Admin\AuthorFilter;
use App\Api\V2\Filter\Admin\SearchFilter;
use App\Api\V2\Filter\Admin\StatusFilter;
use App\Api\V2\Input\Admin\Content\CreateFaqInput;
use App\Api\V2\Input\Admin\Content\UpdateFaqInput;
use App\Api\V2\Metadata\AcceptLanguageParameter;
use App\Api\V2\Metadata\LocaleParameter;
use App\Api\V2\Metadata\OperationSecurityMetadataFactory;
use App\Api\V2\Output\Admin\Global\AdminAuthor;
use App\Api\V2\Output\Admin\Global\AdminTitleTranslation;
use App\Api\V2\Output\Admin\Global\Permissions;
use App\Api\V2\Resource\Admin\Trait\AdminTranslationsTrait;
use App\Api\V2\State\Processor\Admin\Content\CreateAdminFaqProcessor;
use App\Api\V2\State\Processor\Admin\Content\DeleteAdminFaqProcessor;
use App\Api\V2\State\Processor\Admin\Content\UpdateAdminFaqProcessor;
use App\Api\V2\State\Provider\Admin\Content\AdminFaqCollectionProvider;
use App\Api\V2\State\Provider\Admin\Content\AdminFaqProvider;
use App\Enum\Admin\Module\CoreModuleId;
use Symfony\Component\Serializer\Attribute\Groups;

#[
    ApiResource(
        shortName: 'AdminFaq',
        extraProperties: [OperationSecurityMetadataFactory::MODULE => CoreModuleId::FAQ->value],
        normalizationContext: [
            'groups' => ['admin_faq:read', Permissions::GROUP, AdminAuthor::GROUP, AdminTitleTranslation::GROUP],
        ],
        operations: [
            new GetCollection(
                name: 'admin_faq_get_collection',
                uriTemplate: '/admin/faqs',
                errors: [ValidationException::class],
                openapi: new OpenApiOperation(
                    summary: 'Les FAQ',
                    description: 'Titre, recherche et tri par titre dans la langue de contenu, nombre de ' .
                        'catégories, de questions et de pages.',
                ),
                normalizationContext: ['groups' => ['admin_faq:list', Permissions::GROUP, AdminAuthor::GROUP]],
                provider: AdminFaqCollectionProvider::class,
                parameters: [
                    SearchFilter::PARAMETER => new QueryParameter(
                        filter: SearchFilter::class,
                        properties: ['title'],
                        description: 'Recherche dans le titre, sans tenir compte de la casse',
                    ),
                    AdminOrderFilter::PARAMETER => new QueryParameter(
                        filter: AdminOrderFilter::class,
                        properties: ['id', 'title', 'createdAt', 'updatedAt'],
                        description: 'Tri, order[id]=desc par défaut',
                    ),
                    StatusFilter::PARAMETER => new QueryParameter(
                        filter: StatusFilter::class,
                        property: 'disabled',
                        description: 'FAQ actives ou désactivées',
                    ),
                    AuthorFilter::PARAMETER => new QueryParameter(
                        filter: AuthorFilter::class,
                        property: 'author',
                        description: 'FAQ de l\'utilisateur de la session',
                    ),
                    new LocaleParameter(),
                    new AcceptLanguageParameter(),
                ],
            ),
            new Get(
                name: 'admin_faq_get',
                uriTemplate: '/admin/faqs/{id}',
                requirements: ['id' => '\d+'],
                errors: [NotFoundException::class],
                openapi: new OpenApiOperation(
                    summary: 'Une FAQ',
                    description: 'Avec ses catégories et le titre de leurs questions ; la réponse d\'une question ' .
                        'se lit avec la question.',
                ),
                provider: AdminFaqProvider::class,
                parameters: [new LocaleParameter(), new AcceptLanguageParameter()],
            ),
            new Post(
                name: 'admin_faq_post',
                uriTemplate: '/admin/faqs',
                errors: [ValidationException::class],
                openapi: new OpenApiOperation(
                    summary: 'Crée une FAQ',
                    description: 'Titre exigé dans chaque langue ; la FAQ est créée avec une catégorie et une ' .
                        'question aux textes par défaut.',
                ),
                input: CreateFaqInput::class,
                processor: CreateAdminFaqProcessor::class,
                parameters: [new LocaleParameter(), new AcceptLanguageParameter()],
            ),
            new Patch(
                name: 'admin_faq_patch',
                uriTemplate: '/admin/faqs/{id}',
                requirements: ['id' => '\d+'],
                errors: [
                    ForbiddenException::class,
                    NotFoundException::class,
                    PreconditionFailedException::class,
                    ValidationException::class,
                ],
                openapi: new OpenApiOperation(
                    summary: 'Modifie une FAQ',
                    description: 'Un champ absent reste inchangé ; { "disabled": true } désactive la FAQ. ' .
                        'Les catégories et les questions ont leurs propres opérations.',
                ),
                input: UpdateFaqInput::class,
                provider: AdminFaqProvider::class,
                processor: UpdateAdminFaqProcessor::class,
                parameters: [new LocaleParameter(), new AcceptLanguageParameter()],
            ),
            new Delete(
                name: 'admin_faq_delete',
                uriTemplate: '/admin/faqs/{id}',
                requirements: ['id' => '\d+'],
                errors: [
                    ForbiddenException::class,
                    NotFoundException::class,
                    PreconditionFailedException::class,
                    ConflictException::class,
                ],
                openapi: new OpenApiOperation(
                    summary: 'Supprime une FAQ',
                    description: 'Refusé en 409 tant que la FAQ est utilisée par un bloc de page.',
                ),
                provider: AdminFaqProvider::class,
                processor: DeleteAdminFaqProcessor::class,
                parameters: [new LocaleParameter(), new AcceptLanguageParameter()],
            ),
        ],
    ),
]
final class AdminFaq implements VersionedResourceInterface
{
    use AdminTranslationsTrait;

    #[ApiProperty(required: true, identifier: true)]
    #[Groups(['admin_faq:read', 'admin_faq:list'])]
    public int $id = 0;

    /**
     * Titre dans la langue de contenu, à défaut dans la langue par défaut du site
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_faq:read', 'admin_faq:list'])]
    public string $title = '';

    #[ApiProperty(required: true)]
    #[Groups(['admin_faq:read', 'admin_faq:list'])]
    public bool $disabled = false;

    /**
     * Titre de chaque langue du site, dans l'ordre des langues
     * @var AdminTitleTranslation[]
     */
    #[ApiProperty(required: true, genId: false)]
    #[Groups(['admin_faq:read'])]
    public array $translations = [];

    #[ApiProperty(required: true)]
    #[Groups(['admin_faq:read', 'admin_faq:list'])]
    public int $categoryCount = 0;

    #[ApiProperty(required: true)]
    #[Groups(['admin_faq:read', 'admin_faq:list'])]
    public int $questionCount = 0;

    /**
     * Nombre de pages qui l'affichent, une FAQ utilisée ne peut pas être supprimée
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_faq:read', 'admin_faq:list'])]
    public int $pageCount = 0;

    #[ApiProperty(required: true, genId: false)]
    #[Groups(['admin_faq:read', 'admin_faq:list'])]
    public ?AdminAuthor $author = null;

    #[ApiProperty(required: true)]
    #[Groups(['admin_faq:read', 'admin_faq:list'])]
    public ?string $createdAt = null;

    /**
     * Date de la dernière écriture dans la FAQ, catégories et questions comprises
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_faq:read', 'admin_faq:list'])]
    public ?string $updatedAt = null;

    #[ApiProperty(required: true, genId: false)]
    #[Groups(['admin_faq:read', 'admin_faq:list'])]
    public ?Permissions $permissions = null;

    /**
     * Catégories triées, avec le titre de leurs questions
     * @var AdminFaqCategory[]
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_faq:read'])]
    public array $categories = [];

    /**
     * Empreinte des champs propres de la FAQ, sans l'arbre : une écriture dans une catégorie ou une question ne
     * bloque pas le titre ouvert ailleurs
     * @return string|null
     */
    public function getVersion(): ?string
    {
        return json_encode([$this->disabled, $this->getTranslationsFingerprint('title')], JSON_THROW_ON_ERROR);
    }
}
