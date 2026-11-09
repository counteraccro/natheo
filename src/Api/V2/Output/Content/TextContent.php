<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Contenu d'un bloc texte dans l'API v2, rendu en HTML
 */

namespace App\Api\V2\Output\Content;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Attribute\Groups;

final readonly class TextContent
{
    public function __construct(#[Groups(['page_content:read'])] #[ApiProperty(required: true)] public string $html) {}
}
