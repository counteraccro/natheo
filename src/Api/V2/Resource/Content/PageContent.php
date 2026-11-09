<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource API v2 : bloc de contenu d'une page publiée
 */

namespace App\Api\V2\Resource\Content;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\HeaderParameter;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\Validator\Exception\ValidationException;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Filter\LocaleFilter;
use App\Api\V2\Output\Content\FaqContent;
use App\Api\V2\Output\Content\ListingContent;
use App\Api\V2\Output\Content\TextContent;
use App\Api\V2\State\Provider\Content\PageContentProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[
    ApiResource(
        operations: [
            new Get(
                uriTemplate: '/page_contents/{id}',
                errors: [NotFoundException::class, ValidationException::class],
                requirements: ['id' => '\d+'],
                description: 'Le rendu d\'un bloc : HTML (text), questions (faq) ou catégorie à lister (listing)',
                normalizationContext: ['groups' => ['page_content:read']],
                security: "is_granted('ROLE_READ_API')",
                provider: PageContentProvider::class,
                parameters: [
                    'locale' => new QueryParameter(filter: LocaleFilter::class, property: 'locale', description: LocaleFilter::LOCALE_DESCRIPTION),
                    LocaleFilter::HEADER => new HeaderParameter(schema: ['type' => 'string'], description: LocaleFilter::HEADER_DESCRIPTION),
                ],
            ),
        ],
    ),
]
final class PageContent
{
    #[ApiProperty(identifier: true, required: true)]
    #[Groups(['page_content:read', 'page:read'])]
    public int $id = 0;

    /**
     * Type de bloc : text, faq, listing
     */
    #[ApiProperty(required: true)]
    #[Groups(['page_content:read', 'page:read'])]
    public string $type = '';

    /**
     * Emplacement du bloc dans la disposition de la page
     */
    #[ApiProperty(required: true)]
    #[Groups(['page_content:read', 'page:read'])]
    public int $position = 0;

    /**
     * Selon le type du bloc : TextContent (text), FaqContent (faq) ou ListingContent (listing)
     */
    #[ApiProperty(genId: false, required: true)]
    #[Groups(['page_content:read'])]
    public TextContent|FaqContent|ListingContent|null $content = null;
}
