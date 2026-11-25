<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource API v2 d'administration : catégorie d'une FAQ, lue dans l'arbre de sa FAQ
 */

namespace App\Api\V2\Resource\Admin\Content;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use ApiPlatform\Validator\Exception\ValidationException;
use App\Api\V2\Concurrency\VersionedResourceInterface;
use App\Api\V2\Exception\ForbiddenException;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Exception\PreconditionFailedException;
use App\Api\V2\Input\Admin\Content\CreateFaqCategoryInput;
use App\Api\V2\Input\Admin\Content\UpdateFaqCategoryInput;
use App\Api\V2\Metadata\AcceptLanguageParameter;
use App\Api\V2\Metadata\LocaleParameter;
use App\Api\V2\Metadata\OperationSecurityMetadataFactory;
use App\Api\V2\Output\Admin\Global\AdminTitleTranslation;
use App\Api\V2\Output\Admin\Global\Permissions;
use App\Api\V2\Resource\Admin\Trait\AdminTranslationsTrait;
use App\Api\V2\State\Processor\Admin\Content\CreateAdminFaqCategoryProcessor;
use App\Api\V2\State\Processor\Admin\Content\DeleteAdminFaqCategoryProcessor;
use App\Api\V2\State\Processor\Admin\Content\UpdateAdminFaqCategoryProcessor;
use App\Api\V2\State\Provider\Admin\Content\AdminFaqCategoryProvider;
use App\Enum\Admin\Module\CoreModuleId;
use Symfony\Component\Serializer\Attribute\Groups;

#[
    ApiResource(
        shortName: 'AdminFaqCategory',
        extraProperties: [OperationSecurityMetadataFactory::MODULE => CoreModuleId::FAQ->value],
        normalizationContext: [
            'groups' => ['admin_faq_category:read', Permissions::GROUP, AdminTitleTranslation::GROUP],
        ],
        operations: [
            new Get(
                name: 'admin_faq_category_get',
                uriTemplate: '/admin/faq_categories/{id}',
                requirements: ['id' => '\d+'],
                errors: [NotFoundException::class],
                openapi: new OpenApiOperation(
                    summary: 'Une catégorie de FAQ',
                    description: 'Les catégories d\'une FAQ se lisent avec la FAQ.',
                ),
                provider: AdminFaqCategoryProvider::class,
                parameters: [new LocaleParameter(), new AcceptLanguageParameter()],
            ),
            new Post(
                name: 'admin_faq_category_post',
                uriTemplate: '/admin/faq_categories',
                read: false,
                errors: [ForbiddenException::class, NotFoundException::class, ValidationException::class],
                openapi: new OpenApiOperation(
                    summary: 'Crée une catégorie de FAQ',
                    description: 'Ajoutée en dernière position, sans question ; titre exigé dans chaque langue.',
                ),
                input: CreateFaqCategoryInput::class,
                processor: CreateAdminFaqCategoryProcessor::class,
                parameters: [new LocaleParameter(), new AcceptLanguageParameter()],
            ),
            new Patch(
                name: 'admin_faq_category_patch',
                uriTemplate: '/admin/faq_categories/{id}',
                requirements: ['id' => '\d+'],
                errors: [
                    ForbiddenException::class,
                    NotFoundException::class,
                    PreconditionFailedException::class,
                    ValidationException::class,
                ],
                openapi: new OpenApiOperation(
                    summary: 'Modifie une catégorie de FAQ',
                    description: 'Un champ absent reste inchangé. position renumérote les autres catégories ; ' .
                        'désactiver la catégorie désactive ses questions, l\'activer les laisse dans leur état.',
                ),
                input: UpdateFaqCategoryInput::class,
                provider: AdminFaqCategoryProvider::class,
                processor: UpdateAdminFaqCategoryProcessor::class,
                parameters: [new LocaleParameter(), new AcceptLanguageParameter()],
            ),
            new Delete(
                name: 'admin_faq_category_delete',
                uriTemplate: '/admin/faq_categories/{id}',
                requirements: ['id' => '\d+'],
                errors: [ForbiddenException::class, NotFoundException::class, PreconditionFailedException::class],
                openapi: new OpenApiOperation(
                    summary: 'Supprime une catégorie de FAQ',
                    description: 'Ses questions sont supprimées avec elle.',
                ),
                provider: AdminFaqCategoryProvider::class,
                processor: DeleteAdminFaqCategoryProcessor::class,
                parameters: [new LocaleParameter(), new AcceptLanguageParameter()],
            ),
        ],
    ),
]
final class AdminFaqCategory implements VersionedResourceInterface
{
    use AdminTranslationsTrait;

    #[ApiProperty(required: true, identifier: true)]
    #[Groups(['admin_faq_category:read', 'admin_faq:read'])]
    public int $id = 0;

    /**
     * FAQ de la catégorie, par son IRI
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_faq_category:read'])]
    public ?AdminFaq $faq = null;

    /**
     * Position parmi les catégories de la FAQ, à partir de 1
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_faq_category:read', 'admin_faq:read'])]
    public int $position = 0;

    #[ApiProperty(required: true)]
    #[Groups(['admin_faq_category:read', 'admin_faq:read'])]
    public bool $disabled = false;

    /**
     * Titre dans la langue de contenu, à défaut dans la langue par défaut du site
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_faq_category:read', 'admin_faq:read'])]
    public string $title = '';

    /**
     * Titre de chaque langue du site, dans l'ordre des langues
     * @var AdminTitleTranslation[]
     */
    #[ApiProperty(required: true, genId: false)]
    #[Groups(['admin_faq_category:read', 'admin_faq:read'])]
    public array $translations = [];

    /**
     * Questions triées, avec leur titre seulement
     * @var AdminFaqQuestion[]
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_faq:read'])]
    public array $questions = [];

    /**
     * Droits de la FAQ
     */
    #[ApiProperty(required: true, genId: false)]
    #[Groups(['admin_faq_category:read'])]
    public ?Permissions $permissions = null;

    /**
     * Empreinte des champs de la catégorie, position comprise
     * @return string|null
     */
    public function getVersion(): ?string
    {
        return json_encode(
            [$this->position, $this->disabled, $this->getTranslationsFingerprint('title')],
            JSON_THROW_ON_ERROR,
        );
    }
}
