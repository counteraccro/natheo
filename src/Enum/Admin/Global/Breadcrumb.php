<?php
/**
 *
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\Global;

enum Breadcrumb: string
{
    /**
     * Défini le domaine de traduction
     */
    case DOMAIN = 'domain';

    /**
     * Clé du tableau pour construire le fil d'Ariane
     */
    case BREADCRUMB = 'breadcrumb';
}
