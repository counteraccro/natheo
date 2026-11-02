<?php

declare(strict_types=1);
/**
 * Clés des statistiques d'une FAQ
 * @author Gourdon Aymeric
 * @version 2.0
 */

namespace App\Enum\Admin\Content\Faq;

enum FaqStatistiqueKey: string
{
    /**
     * Nombre de catégories
     * @var string
     */
    case NB_CATEGORIES = 'KEY_STAT_NB_CATEGORIES';

    /**
     * Nombre de questions
     * @var string
     */
    case NB_QUESTIONS = 'KEY_STAT_NB_QUESTIONS';
}
