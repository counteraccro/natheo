<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource API v2 d'administration : tag
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
use App\Api\V2\Input\Admin\Content\CreateTagInput;
use App\Api\V2\Input\Admin\Content\UpdateTagInput;
use App\Api\V2\Metadata\AcceptLanguageParameter;
use App\Api\V2\Metadata\LocaleParameter;
use App\Api\V2\Metadata\OperationSecurityMetadataFactory;
use App\Api\V2\Output\Admin\Global\AdminAuthor;
use App\Api\V2\Output\Admin\Global\AdminLabelTranslation;
use App\Api\V2\Output\Admin\Permissions;
use App\Api\V2\State\Processor\Admin\Content\CreateAdminTagProcessor;
use App\Api\V2\State\Processor\Admin\Content\DeleteAdminTagProcessor;
use App\Api\V2\State\Processor\Admin\Content\UpdateAdminTagProcessor;
use App\Api\V2\State\Provider\Admin\Content\AdminTagCollectionProvider;
use App\Api\V2\State\Provider\Admin\Content\AdminTagProvider;
use App\Enum\Admin\Module\CoreModuleId;
use Symfony\Component\Serializer\Attribute\Groups;

#[
    ApiResource(
        shortName: 'AdminTag',
        extraProperties: [OperationSecurityMetadataFactory::MODULE => CoreModuleId::TAG->value],
        normalizationContext: [
            'groups' => ['admin_tag:read', Permissions::GROUP, AdminAuthor::GROUP, AdminLabelTranslation::GROUP],
        ],
        operations: [
            new GetCollection(
                name: 'admin_tag_get_collection',
                uriTemplate: '/admin/tags',
                errors: [ValidationException::class],
                openapi: new OpenApiOperation(
                    summary: 'Les tags',
                    description: 'Libellé, recherche et tri par libellé dans la langue de contenu.',
                ),
                normalizationContext: ['groups' => ['admin_tag:list', Permissions::GROUP, AdminAuthor::GROUP]],
                provider: AdminTagCollectionProvider::class,
                parameters: [
                    SearchFilter::PARAMETER => new QueryParameter(
                        filter: SearchFilter::class,
                        properties: ['label'],
                        description: 'Recherche dans le libellé, sans tenir compte de la casse',
                    ),
                    AdminOrderFilter::PARAMETER => new QueryParameter(
                        filter: AdminOrderFilter::class,
                        properties: ['id', 'label', 'color', 'createdAt', 'updatedAt'],
                        description: 'Tri, order[id]=desc par défaut',
                    ),
                    StatusFilter::PARAMETER => new QueryParameter(
                        filter: StatusFilter::class,
                        property: 'disabled',
                        description: 'Tags actifs ou désactivés',
                    ),
                    AuthorFilter::PARAMETER => new QueryParameter(
                        filter: AuthorFilter::class,
                        property: 'author',
                        description: 'Tags de l\'utilisateur de la session',
                    ),
                    new LocaleParameter(),
                    new AcceptLanguageParameter(),
                ],
            ),
            new Get(
                name: 'admin_tag_get',
                uriTemplate: '/admin/tags/{id}',
                requirements: ['id' => '\d+'],
                errors: [NotFoundException::class],
                openapi: new OpenApiOperation(summary: 'Un tag'),
                provider: AdminTagProvider::class,
                parameters: [new LocaleParameter(), new AcceptLanguageParameter()],
            ),
            new Post(
                name: 'admin_tag_post',
                uriTemplate: '/admin/tags',
                errors: [ValidationException::class],
                openapi: new OpenApiOperation(
                    summary: 'Crée un tag',
                    description: 'Une langue absente reçoit un libellé libre tiré du premier libellé envoyé ' .
                        '(« libellé (en) »), une couleur absente est tirée au hasard.',
                ),
                input: CreateTagInput::class,
                processor: CreateAdminTagProcessor::class,
                parameters: [new LocaleParameter(), new AcceptLanguageParameter()],
            ),
            new Patch(
                name: 'admin_tag_patch',
                uriTemplate: '/admin/tags/{id}',
                requirements: ['id' => '\d+'],
                errors: [
                    ForbiddenException::class,
                    NotFoundException::class,
                    PreconditionFailedException::class,
                    ValidationException::class,
                ],
                openapi: new OpenApiOperation(
                    summary: 'Modifie un tag',
                    description: 'Un champ absent reste inchangé ; { "disabled": true } désactive le tag.',
                ),
                input: UpdateTagInput::class,
                provider: AdminTagProvider::class,
                processor: UpdateAdminTagProcessor::class,
                parameters: [new LocaleParameter(), new AcceptLanguageParameter()],
            ),
            new Delete(
                name: 'admin_tag_delete',
                uriTemplate: '/admin/tags/{id}',
                requirements: ['id' => '\d+'],
                errors: [
                    ForbiddenException::class,
                    NotFoundException::class,
                    PreconditionFailedException::class,
                    ConflictException::class,
                ],
                openapi: new OpenApiOperation(
                    summary: 'Supprime un tag',
                    description: 'Refusé en 409 tant que le tag est rattaché à une page.',
                ),
                provider: AdminTagProvider::class,
                processor: DeleteAdminTagProcessor::class,
                parameters: [new LocaleParameter(), new AcceptLanguageParameter()],
            ),
        ],
    ),
]
final class AdminTag implements VersionedResourceInterface
{
    #[ApiProperty(required: true, identifier: true)]
    #[Groups(['admin_tag:read', 'admin_tag:list'])]
    public int $id = 0;

    /**
     * Libellé dans la langue de contenu, à défaut dans la langue par défaut du site
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_tag:read', 'admin_tag:list'])]
    public string $label = '';

    #[ApiProperty(required: true)]
    #[Groups(['admin_tag:read', 'admin_tag:list'])]
    public string $color = '';

    #[ApiProperty(required: true)]
    #[Groups(['admin_tag:read', 'admin_tag:list'])]
    public bool $disabled = false;

    /**
     * Libellé de chaque langue du site, dans l'ordre des langues
     * @var AdminLabelTranslation[]
     */
    #[ApiProperty(required: true, genId: false)]
    #[Groups(['admin_tag:read'])]
    public array $translations = [];

    /**
     * Nombre de pages rattachées, un tag utilisé ne peut pas être supprimé
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_tag:read', 'admin_tag:list'])]
    public int $pageCount = 0;

    #[ApiProperty(required: true, genId: false)]
    #[Groups(['admin_tag:read', 'admin_tag:list'])]
    public ?AdminAuthor $author = null;

    #[ApiProperty(required: true)]
    #[Groups(['admin_tag:read', 'admin_tag:list'])]
    public ?string $createdAt = null;

    #[ApiProperty(required: true)]
    #[Groups(['admin_tag:read', 'admin_tag:list'])]
    public ?string $updatedAt = null;

    #[ApiProperty(required: true, genId: false)]
    #[Groups(['admin_tag:read', 'admin_tag:list'])]
    public ?Permissions $permissions = null;

    /**
     * Empreinte de la date et du contenu : la date, à la seconde, ne suffit pas à distinguer deux écritures
     * @return string|null
     */
    public function getVersion(): ?string
    {
        $labels = array_map(fn(AdminLabelTranslation $t) => [$t->locale, $t->label], $this->translations);
        return json_encode([$this->updatedAt, $this->color, $this->disabled, $labels], JSON_THROW_ON_ERROR);
    }

    /**
     * Libellé d'une langue, null si absent
     * @param string $locale
     * @return string|null
     */
    public function getLabelByLocale(string $locale): ?string
    {
        foreach ($this->translations as $translation) {
            if ($translation->locale === $locale) {
                return $translation->label;
            }
        }
        return null;
    }
}
