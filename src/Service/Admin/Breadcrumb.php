<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service pour constantes du fil d'Ariane
 */

namespace App\Service\Admin;

class Breadcrumb extends AppAdminService
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
