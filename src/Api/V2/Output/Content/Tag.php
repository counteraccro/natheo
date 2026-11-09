<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tag d'une page dans l'API v2
 */

namespace App\Api\V2\Output\Content;

use Symfony\Component\Serializer\Attribute\Groups;

final readonly class Tag
{
    public function __construct(
        #[Groups(['page:read', 'page:list'])] public string $label,
        #[Groups(['page:read', 'page:list'])] public string $color,
    ) {}
}
