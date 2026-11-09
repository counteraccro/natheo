<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Contenu d'un bloc listing dans l'API v2 : catégorie à lister et URL de la liste paginée
 */

namespace App\Api\V2\Output\Content;

use Symfony\Component\Serializer\Attribute\Groups;

final readonly class ListingContent
{
    public function __construct(
        #[Groups(['page_content:read'])] public string $category,
        #[Groups(['page_content:read'])] public string $title,
        #[Groups(['page_content:read'])] public string $pages,
    ) {}
}
