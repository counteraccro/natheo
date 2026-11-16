<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Traduction d'une page dans l'API v2 : langue, slug et titre
 */

namespace App\Api\V2\Output\Content;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Attribute\Groups;

final readonly class PageTranslation
{
    public function __construct(
        #[Groups(['page:read'])] #[ApiProperty(required: true)] public string $locale,
        #[Groups(['page:read'])] #[ApiProperty(required: true)] public string $slug,
        #[Groups(['page:read'])] #[ApiProperty(required: true)] public string $title,
    ) {}
}
