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
use App\Api\V2\Parameter\LocaleParameter;
use App\Api\V2\State\Provider\Content\PageContentProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[
    ApiResource(
        operations: [
            new Get(
                uriTemplate: '/page_contents/{id}',
                requirements: ['id' => '\d+'],
                description: 'Le rendu d\'un bloc : HTML (text), questions (faq) ou catégorie à lister (listing)',
                normalizationContext: ['groups' => ['page_content:read']],
                security: "is_granted('ROLE_READ_API')",
                provider: PageContentProvider::class,
                parameters: ['locale' => new LocaleParameter()],
            ),
        ],
    ),
]
final class PageContent
{
    #[ApiProperty(identifier: true)]
    #[Groups(['page_content:read', 'page:read'])]
    public int $id = 0;

    /**
     * Type de bloc : text, faq, listing
     */
    #[Groups(['page_content:read', 'page:read'])]
    public string $type = '';

    /**
     * Emplacement du bloc dans la disposition de la page
     */
    #[Groups(['page_content:read', 'page:read'])]
    public int $position = 0;

    /**
     * text : HTML ; faq : {title, categories[{title, questions[{title, answer}]}]} ;
     * listing : {category, title, pages} où pages est l'URL de la liste paginée
     */
    #[ApiProperty(openapiContext: ['oneOf' => [['type' => 'string'], ['type' => 'object']]])]
    #[Groups(['page_content:read'])]
    public string|array|null $content = null;
}
