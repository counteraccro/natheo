<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Balise meta (SEO) d'une page dans l'API v2
 */

namespace App\Api\V2\Output\Content;

use Symfony\Component\Serializer\Attribute\Groups;

final readonly class Meta
{
    public function __construct(
        #[Groups(['page:read'])] public string $name,
        #[Groups(['page:read'])] public string $value,
    ) {}
}
