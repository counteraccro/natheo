<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module qui affiche un nombre à côté de certaines de ses entrées de menu
 */

namespace App\Service\Admin\Module;

interface AdminMenuBadgeInterface
{
    /**
     * Nombres à afficher, indexés par identifiant d'entrée du module
     * @return array<string, int>
     */
    public function getBadges(): array;
}
