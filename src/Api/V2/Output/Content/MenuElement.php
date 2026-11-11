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
        #[Groups(['menu:read', 'menu:list', 'page:read'])] #[ApiProperty(required: true)] public int $id,
        #[Groups(['menu:read', 'menu:list', 'page:read'])] #[ApiProperty(required: true)] public string $label,
        #[Groups(['menu:read', 'menu:list', 'page:read'])] #[ApiProperty(required: true)] public string $target,
        #[Groups(['menu:read', 'menu:list', 'page:read'])] #[ApiProperty(required: true)] public ?string $url,
        #[Groups(['menu:read', 'menu:list', 'page:read'])] #[ApiProperty(required: true)] public ?string $pageSlug,
        #[Groups(['menu:read', 'menu:list', 'page:read'])] #[ApiProperty(required: true)] public ?string $pageCategory,
        #[Groups(['menu:read', 'menu:list', 'page:read'])] #[ApiProperty(required: true)] public int $column,
        #[Groups(['menu:read', 'menu:list', 'page:read'])] #[ApiProperty(required: true)] public int $row,
        #[Groups(['menu:read', 'menu:list', 'page:read'])] #[ApiProperty(genId: false, required: true)] public array $children = [],
    ) {}
}
