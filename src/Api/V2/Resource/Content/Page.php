<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource API v2 : page publiée
 */

namespace App\Api\V2\Resource\Content;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\HeaderParameter;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use ApiPlatform\Validator\Exception\ValidationException;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Metadata\EnumSlugPropertyMetadataFactory;
use App\Api\V2\Output\Content\Author;
use App\Api\V2\Output\Content\Meta;
use App\Api\V2\Output\Content\Tag;
use App\Api\V2\Filter\LocaleFilter;
use App\Api\V2\Filter\PageCategoryFilter;
use App\Api\V2\State\Provider\Content\PageCollectionProvider;
use App\Api\V2\State\Provider\Content\PageProvider;
use App\Enum\Admin\Content\Page\PageCategory;
use App\Enum\Admin\Content\Page\PageRender;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[
    ApiResource(
        operations: [
            new Get(
                uriTemplate: '/pages/{id}',
                requirements: ['id' => '\d+'],
                errors: [NotFoundException::class, ValidationException::class],
                openapi: new OpenApiOperation(summary: 'Une page publiée par son id'),
                normalizationContext: ['groups' => ['page:read']],
                security: "is_granted('ROLE_READ_API')",
                provider: PageProvider::class,
                parameters: [
                    'locale' => new QueryParameter(
                        filter: LocaleFilter::class,
                        property: 'locale',
                        description: LocaleFilter::LOCALE_DESCRIPTION,
                    ),
                    LocaleFilter::HEADER => new HeaderParameter(
                        schema: ['type' => 'string'],
                        description: LocaleFilter::HEADER_DESCRIPTION,
                    ),
                ],
            ),
            new GetCollection(
                uriTemplate: '/pages',
                errors: [ValidationException::class],
                openapi: new OpenApiOperation(summary: 'Les pages publiées, les plus récemment modifiées en premier'),
                normalizationContext: ['groups' => ['page:list']],
                security: "is_granted('ROLE_READ_API')",
                provider: PageCollectionProvider::class,
                parameters: [
                    'locale' => new QueryParameter(
                        filter: LocaleFilter::class,
                        property: 'locale',
                        description: LocaleFilter::LOCALE_DESCRIPTION,
                    ),
                    LocaleFilter::HEADER => new HeaderParameter(
                        schema: ['type' => 'string'],
                        description: LocaleFilter::HEADER_DESCRIPTION,
                    ),
                    'category' => new QueryParameter(
                        filter: PageCategoryFilter::class,
                        description: 'Catégorie des pages',
                    ),
                    'tag' => new QueryParameter(
                        schema: ['type' => 'integer', 'minimum' => 1],
                        property: 'tags',
                        description: 'Id d\'un tag actif',
                        constraints: [new Assert\Regex(pattern: '/^[1-9][0-9]*$/')],
                    ),
                    'slug' => new QueryParameter(
                        schema: ['type' => 'string', 'minLength' => 1],
                        description: 'URL (slug) exacte : au plus une page, en priorité dans la langue demandée',
                    ),
                    'landing' => new QueryParameter(
                        schema: ['type' => 'boolean'],
                        property: 'landingPage',
                        description: 'true : la page d\'accueil seule, false : les autres pages',
                        castToNativeType: true,
                    ),
                ],
            ),
        ],
    ),
]
final class Page
{
    #[ApiProperty(required: true, identifier: true)]
    #[Groups(['page:read', 'page:list'])]
    public int $id = 0;

    #[ApiProperty(required: true)]
    #[Groups(['page:read', 'page:list'])]
    public string $slug = '';

    #[ApiProperty(required: true)]
    #[Groups(['page:read', 'page:list'])]
    public string $title = '';

    #[
        ApiProperty(
            required: true,
            extraProperties: [EnumSlugPropertyMetadataFactory::EXTRA_PROPERTY => PageCategory::class],
        ),
    ]
    #[Groups(['page:read', 'page:list'])]
    public string $category = '';

    #[ApiProperty(required: true)]
    #[Groups(['page:read', 'page:list'])]
    public ?string $headerImage = null;

    #[ApiProperty(genId: false, required: true)]
    #[Groups(['page:read', 'page:list'])]
    public ?Author $author = null;

    /**
     * @var Tag[]
     */
    #[ApiProperty(genId: false, required: true)]
    #[Groups(['page:read', 'page:list'])]
    public array $tags = [];

    #[ApiProperty(required: true)]
    #[Groups(['page:read', 'page:list'])]
    public ?\DateTimeInterface $createdAt = null;

    #[ApiProperty(required: true)]
    #[Groups(['page:read', 'page:list'])]
    public ?\DateTimeInterface $updatedAt = null;

    /**
     * Disposition des blocs (one_block, two_block…)
     */
    #[
        ApiProperty(
            required: true,
            extraProperties: [EnumSlugPropertyMetadataFactory::EXTRA_PROPERTY => PageRender::class],
        ),
    ]
    #[Groups(['page:read'])]
    public string $render = '';

    #[ApiProperty(required: true)]
    #[Groups(['page:read'])]
    public bool $landingPage = false;

    #[ApiProperty(required: true)]
    #[Groups(['page:read'])]
    public bool $openComment = false;

    #[ApiProperty(required: true)]
    #[Groups(['page:read'])]
    public int $reads = 0;

    /**
     * @var Meta[]
     */
    #[ApiProperty(genId: false, required: true)]
    #[Groups(['page:read'])]
    public array $seo = [];

    /**
     * @var PageContent[]
     */
    #[ApiProperty(required: true)]
    #[Groups(['page:read'])]
    public array $contents = [];

    /**
     * Menus affichés sur la page : ceux de la page puis les menus par défaut des positions libres
     * @var Menu[]
     */
    #[ApiProperty(required: true)]
    #[Groups(['page:read'])]
    public array $menus = [];
}
