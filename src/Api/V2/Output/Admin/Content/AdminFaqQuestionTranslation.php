<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Titre et réponse d'une question de FAQ dans une langue
 */

namespace App\Api\V2\Output\Admin\Content;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Attribute\Groups;

final readonly class AdminFaqQuestionTranslation
{
    /**
     * Groupe de sérialisation des traductions d'une question
     * @var string
     */
    public const string GROUP = 'admin:faq_question_translation';

    /**
     * @param string $locale
     * @param string $title
     * @param string $answer réponse en Markdown
     */
    public function __construct(
        #[Groups([self::GROUP])] #[ApiProperty(required: true)] public string $locale,
        #[Groups([self::GROUP])] #[ApiProperty(required: true)] public string $title,
        #[Groups([self::GROUP])] #[ApiProperty(required: true)] public string $answer,
    ) {}
}
