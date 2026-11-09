<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Élément d'un menu dans l'API v2 : lien vers une page (pageSlug) ou lien externe (url)
 */

namespace App\Api\V2\Output\Content;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Attribute\Groups;

final readonly class MenuElement
{
    /**
     * @param MenuElement[] $children
     */
    public function __construct(
        #[Groups(['menu:read', 'page:read'])] public int $id,
        #[Groups(['menu:read', 'page:read'])] public string $label,
        #[Groups(['menu:read', 'page:read'])] public string $target,
        #[Groups(['menu:read', 'page:read'])] public ?string $url,
        #[Groups(['menu:read', 'page:read'])] public ?string $pageSlug,
        #[Groups(['menu:read', 'page:read'])] public ?string $pageCategory,
        #[Groups(['menu:read', 'page:read'])] public int $column,
        #[Groups(['menu:read', 'page:read'])] public int $row,
        #[Groups(['menu:read', 'page:read'])] #[ApiProperty(genId: false)] public array $children = [],
    ) {}
}
