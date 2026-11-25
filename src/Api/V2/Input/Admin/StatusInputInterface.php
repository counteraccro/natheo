<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Input de modification d'un élément qui peut être activé ou désactivé
 */

namespace App\Api\V2\Input\Admin;

interface StatusInputInterface
{
    /**
     * Statut demandé, null s'il reste inchangé
     * @return bool|null
     */
    public function getDisabled(): ?bool;

    /**
     * true quand seul le statut est envoyé
     * @return bool
     */
    public function isStatusOnly(): bool;
}
