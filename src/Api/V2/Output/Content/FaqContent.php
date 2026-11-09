<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Contenu d'un bloc FAQ dans l'API v2
 */

namespace App\Api\V2\Output\Content;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Attribute\Groups;

final readonly class FaqContent
{
    /**
     * @param FaqContentCategory[] $categories
     */
    public function __construct(
        #[Groups(['page_content:read'])] #[ApiProperty(required: true)] public string $title,
        #[Groups(['page_content:read'])] #[ApiProperty(genId: false, required: true)] public array $categories,
    ) {}
}
