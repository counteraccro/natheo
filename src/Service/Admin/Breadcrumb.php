<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service pour constantes du fil d'Ariane
 */

namespace App\Service\Admin;

use App\Controller\Admin\AppAdminController;

class Breadcrumb extends AppAdminController
{
    /**
     * Défini le domaine de traduction
     */
    const DOMAIN = 'domain';

    /**
     * Clé du tableau pour construire le fil d'Ariane
     */
    const BREADCRUMB = 'breadcrumb';
}
