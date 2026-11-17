<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Définition d'un module du back-office
 */

namespace App\Service\Admin\Module;

final readonly class AdminModule
{
    /**
     * @param string $id identifiant, repris par l'extraProperties « module » de ses opérations d'API
     * @param string $role rôle minimal de l'utilisateur pour accéder au module
     */
    public function __construct(public string $id, public string $role) {}
}
