<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource API v2 d'administration : question d'une FAQ, son titre étant lu dans l'arbre de sa FAQ
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
use App\Api\V2\Input\Admin\Content\CreateFaqQuestionInput;
use App\Api\V2\Input\Admin\Content\UpdateFaqQuestionInput;
use App\Api\V2\Metadata\AcceptLanguageParameter;
use App\Api\V2\Metadata\LocaleParameter;
use App\Api\V2\Metadata\OperationSecurityMetadataFactory;
use App\Api\V2\Output\Admin\Content\AdminFaqQuestionTranslation;
use App\Api\V2\Output\Admin\Global\Permissions;
use App\Api\V2\State\Processor\Admin\Content\CreateAdminFaqQuestionProcessor;
use App\Api\V2\State\Processor\Admin\Content\DeleteAdminFaqQuestionProcessor;
use App\Api\V2\State\Processor\Admin\Content\UpdateAdminFaqQuestionProcessor;
use App\Api\V2\State\Provider\Admin\Content\AdminFaqQuestionProvider;
use App\Enum\Admin\Module\CoreModuleId;
use Symfony\Component\Serializer\Attribute\Groups;

#[
    ApiResource(
        shortName: 'AdminFaqQuestion',
        extraProperties: [OperationSecurityMetadataFactory::MODULE => CoreModuleId::FAQ->value],
        normalizationContext: [
            'groups' => ['admin_faq_question:read', Permissions::GROUP, AdminFaqQuestionTranslation::GROUP],
        ],
        operations: [
            new Get(
                name: 'admin_faq_question_get',
                uriTemplate: '/admin/faq_questions/{id}',
                requirements: ['id' => '\d+'],
                errors: [NotFoundException::class],
                openapi: new OpenApiOperation(
                    summary: 'Une question de FAQ',
                    description: 'Titre et réponse en Markdown de chaque langue.',
                ),
                provider: AdminFaqQuestionProvider::class,
                parameters: [new LocaleParameter(), new AcceptLanguageParameter()],
            ),
            new Post(
                name: 'admin_faq_question_post',
                uriTemplate: '/admin/faq_questions',
                read: false,
                errors: [ForbiddenException::class, NotFoundException::class, ValidationException::class],
                openapi: new OpenApiOperation(
                    summary: 'Crée une question de FAQ',
                    description: 'Ajoutée en dernière position de sa catégorie ; titre et réponse exigés dans ' .
                        'chaque langue.',
                ),
                input: CreateFaqQuestionInput::class,
                processor: CreateAdminFaqQuestionProcessor::class,
                parameters: [new LocaleParameter(), new AcceptLanguageParameter()],
            ),
            new Patch(
                name: 'admin_faq_question_patch',
                uriTemplate: '/admin/faq_questions/{id}',
                requirements: ['id' => '\d+'],
                errors: [
                    ForbiddenException::class,
                    NotFoundException::class,
                    PreconditionFailedException::class,
                    ValidationException::class,
                ],
                openapi: new OpenApiOperation(
                    summary: 'Modifie une question de FAQ',
                    description: 'Un champ absent reste inchangé. category déplace la question vers une autre ' .
                        'catégorie de la même FAQ, position la place dans sa catégorie.',
                ),
                input: UpdateFaqQuestionInput::class,
                provider: AdminFaqQuestionProvider::class,
                processor: UpdateAdminFaqQuestionProcessor::class,
                parameters: [new LocaleParameter(), new AcceptLanguageParameter()],
            ),
            new Delete(
                name: 'admin_faq_question_delete',
                uriTemplate: '/admin/faq_questions/{id}',
                requirements: ['id' => '\d+'],
                errors: [ForbiddenException::class, NotFoundException::class, PreconditionFailedException::class],
                openapi: new OpenApiOperation(summary: 'Supprime une question de FAQ'),
                provider: AdminFaqQuestionProvider::class,
                processor: DeleteAdminFaqQuestionProcessor::class,
                parameters: [new LocaleParameter(), new AcceptLanguageParameter()],
            ),
        ],
    ),
]
final class AdminFaqQuestion implements VersionedResourceInterface
{
    #[ApiProperty(required: true, identifier: true)]
    #[Groups(['admin_faq_question:read', 'admin_faq:read'])]
    public int $id = 0;

    /**
     * FAQ de la question, par son IRI
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_faq_question:read'])]
    public ?AdminFaq $faq = null;

    /**
     * Catégorie de la question, par son IRI
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_faq_question:read'])]
    public ?AdminFaqCategory $category = null;

    /**
     * Position dans sa catégorie, à partir de 1
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_faq_question:read', 'admin_faq:read'])]
    public int $position = 0;

    #[ApiProperty(required: true)]
    #[Groups(['admin_faq_question:read', 'admin_faq:read'])]
    public bool $disabled = false;

    /**
     * Titre dans la langue de contenu, à défaut dans la langue par défaut du site
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_faq_question:read', 'admin_faq:read'])]
    public string $title = '';

    /**
     * Titre et réponse de chaque langue du site, dans l'ordre des langues
     * @var AdminFaqQuestionTranslation[]
     */
    #[ApiProperty(required: true, genId: false)]
    #[Groups(['admin_faq_question:read'])]
    public array $translations = [];

    /**
     * Droits de la FAQ
     */
    #[ApiProperty(required: true, genId: false)]
    #[Groups(['admin_faq_question:read'])]
    public ?Permissions $permissions = null;

    /**
     * Empreinte des champs de la question, catégorie et position comprises
     * @return string|null
     */
    public function getVersion(): ?string
    {
        $texts = array_map(
            fn(AdminFaqQuestionTranslation $t) => [$t->locale, $t->title, $t->answer],
            $this->translations,
        );
        return json_encode([$this->category?->id, $this->position, $this->disabled, $texts], JSON_THROW_ON_ERROR);
    }

    /**
     * Titre d'une langue, null si absent
     * @param string $locale
     * @return string|null
     */
    public function getTitleByLocale(string $locale): ?string
    {
        foreach ($this->translations as $translation) {
            if ($translation->locale === $locale) {
                return $translation->title;
            }
        }
        return null;
    }
}
