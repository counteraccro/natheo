<?php

declare(strict_types=1);
/**
 * Actions de mise à jour d'une statistique de FAQ
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\Content\Faq;

enum FaqStatistiqueAction: string
{
    /**
     * Ajoute la valeur
     * @var string
     */
    case ADD = 'add';

    /**
     * Soustrait la valeur (minimum 0)
     * @var string
     */
    case SUB = 'sub';

    /**
     * Remplace la valeur
     * @var string
     */
    case OVERWRITE = 'overwrite';
}
