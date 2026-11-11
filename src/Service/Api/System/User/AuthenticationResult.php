<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Résultat d'une tentative d'authentification d'un utilisateur par l'API
 */

namespace App\Service\Api\System\User;

use App\Entity\Admin\System\User;

final readonly class AuthenticationResult
{
    /**
     * @param User|null $user utilisateur authentifié, null si refusé
     * @param int|null $retryAfter secondes avant une nouvelle tentative, si la limite est atteinte
     */
    public function __construct(public ?User $user = null, public ?int $retryAfter = null) {}

    /**
     * Limite de tentatives atteinte
     * @return bool
     */
    public function isThrottled(): bool
    {
        return $this->retryAfter !== null;
    }
}
