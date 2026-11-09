<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Auteur d'une page dans l'API v2
 */

namespace App\Api\V2\Output\Content;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Attribute\Groups;

final readonly class Author
{
    public function __construct(
        #[Groups(['page:read', 'page:list'])] #[ApiProperty(required: true)] public string $name,
        #[Groups(['page:read', 'page:list'])] #[ApiProperty(required: true)] public ?string $description,
        #[Groups(['page:read', 'page:list'])] #[ApiProperty(required: true)] public ?string $avatar,
    ) {}
}
