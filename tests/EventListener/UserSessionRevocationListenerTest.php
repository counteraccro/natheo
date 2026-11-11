<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de la révocation des sessions API au changement de mot de passe et à la désactivation du compte
 */

namespace App\Tests\EventListener;

use App\Entity\Admin\System\User;
use App\Entity\Admin\System\UserSession;
use App\Service\Admin\System\User\UserService;
use App\Tests\AppWebTestCase;

class UserSessionRevocationListenerTest extends AppWebTestCase
{
    /**
     * Autre modification du compte : sessions conservées
     * @return void
     */
    public function testOtherChangeKeepsSessions(): void
    {
        $user = $this->createUserWithSession();
        $user->setLogin(self::getFaker()->userName());
        $this->persistAndFlush($user);

        $this->assertSessionCount(1, $user);
    }

    /**
     * Changement de mot de passe : sessions révoquées
     * @return void
     */
    public function testPasswordChangeRevokesSessions(): void
    {
        $user = $this->createUserWithSession();
        $this->container->get(UserService::class)->updatePassword($user, 'Nouveau-mot-de-passe-1');

        $this->assertSessionCount(0, $user);
    }

    /**
     * Désactivation : sessions révoquées ; réactivation sans effet
     * @return void
     */
    public function testDisableRevokesSessions(): void
    {
        $user = $this->createUserWithSession();
        $user->setDisabled(true);
        $this->persistAndFlush($user);
        $this->assertSessionCount(0, $user);

        $this->createUserSession(['user' => $user]);
        $user->setDisabled(false);
        $this->persistAndFlush($user);
        $this->assertSessionCount(1, $user);
    }

    /**
     * Utilisateur actif avec une session
     * @return User
     */
    private function createUserWithSession(): User
    {
        $user = $this->createUserContributeur(['disabled' => false]);
        $this->createUserSession(['user' => $user]);
        $this->assertSessionCount(1, $user);
        return $user;
    }

    /**
     * @param int $expected
     * @param User $user
     * @return void
     */
    private function assertSessionCount(int $expected, User $user): void
    {
        $this->assertSame($expected, $this->em->getRepository(UserSession::class)->count(['user' => $user]));
    }
}
