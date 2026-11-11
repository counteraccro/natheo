<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Convertit l'utilisateur de la session en ressource Me de l'API v2
 */

namespace App\Api\V2\Mapper\System;

use App\Api\V2\Resource\System\Me;
use App\Entity\Admin\System\User;

final readonly class MeMapper
{
    /**
     * @param User $user
     * @param string|null $avatar chemin public de l'avatar
     * @param bool $canModerate
     * @return Me
     */
    public function map(User $user, ?string $avatar, bool $canModerate): Me
    {
        $me = new Me();
        $me->id = (int) $user->getId();
        $me->login = (string) $user->getLogin();
        $me->email = (string) $user->getEmail();
        $me->firstname = $user->getFirstname();
        $me->lastname = $user->getLastname();
        $me->roles = array_values($user->getRoles());
        $me->avatar = $avatar;
        $me->canModerate = $canModerate;
        return $me;
    }
}
