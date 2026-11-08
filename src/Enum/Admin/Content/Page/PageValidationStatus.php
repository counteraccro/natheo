<?php

declare(strict_types=1);
/**
 * État de validation d'une page : de la page jamais publiée, ou du brouillon d'une page publiée
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\Content\Page;

enum PageValidationStatus: int
{
    /**
     * Aucune demande de validation
     */
    case NONE = 0;

    /**
     * Soumise, en attente de validation
     */
    case PENDING = 1;

    /**
     * Refusée, modifiable puis soumise à nouveau
     */
    case REFUSED = 2;
}
