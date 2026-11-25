<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Titre et réponse d'une question de FAQ dans une langue, envoyés à l'API
 */

namespace App\Api\V2\Input\Admin\Content;

use ApiPlatform\Metadata\ApiProperty;

final class FaqQuestionTranslationInput
{
    #[ApiProperty(required: true)]
    public string $locale = '';

    #[ApiProperty(required: true)]
    public string $title = '';

    /**
     * Réponse en Markdown
     */
    #[ApiProperty(required: true)]
    public string $answer = '';
}
