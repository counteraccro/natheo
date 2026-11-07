<?php

declare(strict_types=1);
/**
 * Rôles des utilisateurs du back-office
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\System\User;

use App\Entity\Admin\System\User;

enum Role: string
{
    case USER = 'ROLE_USER';

    case CONTRIBUTEUR = 'ROLE_CONTRIBUTEUR';

    case ADMIN = 'ROLE_ADMIN';

    case SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    /**
     * Vérifie si le rôle est directement attribué à l'utilisateur, sans la hiérarchie de isGranted()
     * @param User $user
     * @return bool
     */
    public function isGrantedTo(User $user): bool
    {
        return in_array($this->value, $user->getRoles(), true);
    }

    /**
     * Retourne la liste des rôles pour les formulaires [clé de traduction => rôle]
     * @return array<string, string>
     */
    public static function choices(): array
    {
        return [
            'user.form_update.role.user' => self::USER->value,
            'user.form_update.role.contributor' => self::CONTRIBUTEUR->value,
            'user.form_update.role.admin' => self::ADMIN->value,
            'user.form_update.role.super_admin' => self::SUPER_ADMIN->value,
        ];
    }
}
