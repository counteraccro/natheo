<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Catégorie de questions d'un bloc FAQ dans l'API v2
 */

namespace App\Api\V2\Output\Content;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Attribute\Groups;

final readonly class FaqContentCategory
{
    /**
     * @param FaqContentQuestion[] $questions
     */
    public function __construct(
        #[Groups(['page_content:read'])] public string $title,
        #[Groups(['page_content:read'])] #[ApiProperty(genId: false)] public array $questions,
    ) {}
}
