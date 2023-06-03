<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de savoir si le User possède un role ou non
 * Pour controller avec la hiérarchie des droits utilisés isGranted
 */

namespace App\Utils\User;

use App\Entity\Admin\User;

class Role
{
    /**
     * Role User
     * @const
     */
    const ROLE_USER = 'ROLE_USER';

    /**
     * Role contributeur
     * @const
     */
    const ROLE_CONTRIBUTEUR = 'ROLE_CONTRIBUTEUR';

    /**
     * Role Admin
     * @const
     */
    const ROLE_ADMIN = 'ROLE_ADMIN';

    /**
     * Role super admin
     * @const
     */
    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    /**
     * @var User
     */
    private User $user;

    /**
     * Constructeur de la class ROLE
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * L'utilisateur est-il superadmin ?
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->isRole(self::ROLE_SUPER_ADMIN);
    }

    /**
     * L'utilisateur est-il admin ?
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->isRole(self::ROLE_ADMIN);
    }

    /**
     * L'utilisateur est-il contributeur ?
     * @return bool
     */
    public function isContributeur(): bool
    {
        return $this->isRole(self::ROLE_CONTRIBUTEUR);
    }

    /**
     * L'utilisateur est-il user ?
     * @return bool
     */
    public function isUser(): bool
    {
        return $this->isRole(self::ROLE_USER);
    }

    /**
     * Permet de savoir si le user possède le role envoyé en paramètre
     * @param String $role
     * @return bool
     */
    private function isRole(String $role):bool
    {
        return in_array($role, $this->user->getRoles());
    }

    /**
     * Retourne un tableau de role
     * @return array
     */
    public static function getListRole(): array
    {
        return [
            'user.form_update.role.user' => self::ROLE_USER,
            'user.form_update.role.contributeur' => self::ROLE_CONTRIBUTEUR,
            'user.form_update.role.admin' => self::ROLE_ADMIN,
            'user.form_update.role.super_admin' => self::ROLE_SUPER_ADMIN,
        ];
    }
}
