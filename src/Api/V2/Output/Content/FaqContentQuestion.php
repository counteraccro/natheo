<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Question d'un bloc FAQ dans l'API v2, réponse en HTML
 */

namespace App\Api\V2\Output\Content;

use Symfony\Component\Serializer\Attribute\Groups;

final readonly class FaqContentQuestion
{
    public function __construct(
        #[Groups(['page_content:read'])] public string $title,
        #[Groups(['page_content:read'])] public string $answer,
    ) {}
}
