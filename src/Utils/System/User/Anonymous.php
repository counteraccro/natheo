<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet d'anonymiser un utilisateur
 */

namespace App\Utils\System\User;

use App\Entity\Admin\System\User;
use Exception;

class Anonymous
{
    /**
     * @var User
     */
    private User $user;

    /**
     * @const
     */
    const FIRST_NAME = 'John';

    /**
     * @const
     */
    const LAST_NAME = 'Doe';

    /**
     * @const
     */
    const LOGIN = 'John Doe';

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Anonymise l'utilisateur courant
     * @return User
     * @throws Exception
     */
    public function anonymizer(): User
    {
        $this->user->setFirstname(self::FIRST_NAME);
        $this->user->setLastname(self::LAST_NAME);
        $this->user->setLogin(self::LOGIN);
        $this->user->setRoles([Role::ROLE_USER]);
        $email = $this->randomStr(24) . '@' . $this->randomStr(6) . '.com';
        $this->user->setEmail($email);
        $this->user->setPassword($this->randomStr(40));
        $this->user->setDisabled(true);
        $this->user->setAnonymous(true);
        $this->user->setFounder(false);
        $this->user->removeAllOptionsUser($this->user->getOptionsUser());
        return $this->user;
    }

    /**
     * Génère une string aléatoire
     * @param int $length
     * @param string $keyspace
     * @return string
     * @throws Exception
     */
    private function randomStr(
        int $length = 16,
        string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
    ): string {
        if ($length < 1) {
            throw new \RangeException('Length must be a positive integer');
        }
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces[] = $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }
}
