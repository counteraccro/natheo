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
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use App\Api\V2\Output\Content\Author;
use App\Api\V2\Output\Content\Meta;
use App\Api\V2\Output\Content\Tag;
use App\Api\V2\Parameter\LocaleParameter;
use App\Api\V2\State\Processor\Content\ReadPageProcessor;
use App\Api\V2\State\Provider\Content\PageCollectionProvider;
use App\Api\V2\State\Provider\Content\PageProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[
    ApiResource(
        operations: [
            new Get(
                uriTemplate: '/pages/{id}',
                requirements: ['id' => '\d+'],
                description: 'Une page publiée par son id',
                normalizationContext: ['groups' => ['page:read']],
                security: "is_granted('ROLE_READ_API')",
                provider: PageProvider::class,
                parameters: ['locale' => new LocaleParameter()],
            ),
            new Get(
                uriTemplate: '/pages/slug/{slug}',
                uriVariables: ['slug'],
                description: 'Une page publiée par son URL (slug), en priorité dans la langue demandée',
                normalizationContext: ['groups' => ['page:read'], 'item_uri_template' => '/pages/{id}'],
                security: "is_granted('ROLE_READ_API')",
                provider: PageProvider::class,
                parameters: ['locale' => new LocaleParameter()],
            ),
            new Get(
                uriTemplate: '/pages/landing',
                uriVariables: [],
                description: 'La page d\'accueil du site',
                normalizationContext: ['groups' => ['page:read'], 'item_uri_template' => '/pages/{id}'],
                security: "is_granted('ROLE_READ_API')",
                provider: PageProvider::class,
                parameters: ['locale' => new LocaleParameter()],
            ),
            new GetCollection(
                uriTemplate: '/pages',
                description: 'Les pages publiées, les plus récemment modifiées en premier',
                normalizationContext: ['groups' => ['page:list']],
                security: "is_granted('ROLE_READ_API')",
                provider: PageCollectionProvider::class,
                parameters: [
                    'locale' => new LocaleParameter(),
                    'category' => new QueryParameter(
                        schema: ['type' => 'string'],
                        description: 'Catégorie : slug (blog, evenement…) ou libellé traduit',
                    ),
                    'tag' => new QueryParameter(
                        schema: ['type' => 'string'],
                        description: 'Libellé de tag (recherche partielle, insensible à la casse)',
                    ),
                ],
            ),
            new Post(
                uriTemplate: '/pages/{id}/read',
                uriVariables: ['id'],
                requirements: ['id' => '\d+'],
                status: 204,
                description: 'Compte une lecture de la page',
                security: "is_granted('ROLE_READ_API')",
                input: false,
                output: false,
                read: false,
                deserialize: false,
                validate: false,
                processor: ReadPageProcessor::class,
            ),
        ],
    ),
]
final class Page
{
    #[ApiProperty(identifier: true)]
    #[Groups(['page:read', 'page:list'])]
    public int $id = 0;

    #[ApiProperty(identifier: false)]
    #[Groups(['page:read', 'page:list'])]
    public string $slug = '';

    #[Groups(['page:read', 'page:list'])]
    public string $title = '';

    #[Groups(['page:read', 'page:list'])]
    public string $category = '';

    #[Groups(['page:read', 'page:list'])]
    public ?string $headerImage = null;

    #[ApiProperty(genId: false)]
    #[Groups(['page:read', 'page:list'])]
    public ?Author $author = null;

    /**
     * @var Tag[]
     */
    #[ApiProperty(genId: false)]
    #[Groups(['page:read', 'page:list'])]
    public array $tags = [];

    #[Groups(['page:read', 'page:list'])]
    public ?\DateTimeInterface $createdAt = null;

    #[Groups(['page:read', 'page:list'])]
    public ?\DateTimeInterface $updatedAt = null;

    /**
     * Disposition des blocs (one_block, two_block…)
     */
    #[Groups(['page:read'])]
    public string $render = '';

    #[Groups(['page:read'])]
    public bool $landingPage = false;

    #[Groups(['page:read'])]
    public bool $openComment = false;

    #[Groups(['page:read'])]
    public int $reads = 0;

    /**
     * @var Meta[]
     */
    #[ApiProperty(genId: false)]
    #[Groups(['page:read'])]
    public array $seo = [];

    /**
     * @var PageContent[]
     */
    #[Groups(['page:read'])]
    public array $contents = [];

    /**
     * Menus affichés sur la page : ceux de la page puis les menus par défaut des positions libres
     * @var Menu[]
     */
    #[Groups(['page:read'])]
    public array $menus = [];
}
