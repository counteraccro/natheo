<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tag actif d'une page dans l'API v2, id utilisable comme filtre des pages
 */

namespace App\Api\V2\Output\Content;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Attribute\Groups;

final readonly class Tag
{
    public function __construct(
        #[Groups(['page:read', 'page:list'])] #[ApiProperty(required: true)] public int $id,
        #[Groups(['page:read', 'page:list'])] #[ApiProperty(required: true)] public string $label,
        #[Groups(['page:read', 'page:list'])] #[ApiProperty(required: true)] public string $color,
    ) {}
}
