<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests du repository des sessions API des utilisateurs
 */

namespace App\Tests\Repository\Admin\System;

use App\Entity\Admin\System\UserSession;
use App\Repository\Admin\System\UserSessionRepository;
use App\Tests\AppWebTestCase;

class UserSessionRepositoryTest extends AppWebTestCase
{
    /**
     * Test méthode findActiveByUser() : sessions non expirées de l'utilisateur, la plus récemment utilisée en premier
     * @return void
     */
    public function testFindActiveByUser(): void
    {
        $user = $this->createUserContributeur();
        $old = $this->createUserSession(['user' => $user, 'lastUsedAt' => new \DateTimeImmutable('-1 day')]);
        $recent = $this->createUserSession(['user' => $user, 'lastUsedAt' => new \DateTimeImmutable('-1 minute')]);
        $this->createUserSession(['user' => $user, 'refreshTokenExpiresAt' => new \DateTimeImmutable('-1 second')]);
        $this->createUserSession();

        $ids = array_map(
            fn(UserSession $session) => $session->getId(),
            $this->getRepository()->findActiveByUser($user),
        );
        $this->assertSame([$recent->getId(), $old->getId()], $ids);
    }

    /**
     * Test méthode deleteByUser() : toutes les sessions, ou seulement les expirées
     * @return void
     */
    public function testDeleteByUser(): void
    {
        $user = $this->createUserContributeur();
        $active = $this->createUserSession(['user' => $user]);
        $this->createUserSession(['user' => $user, 'refreshTokenExpiresAt' => new \DateTimeImmutable('-1 second')]);
        $other = $this->createUserSession();
        $repository = $this->getRepository();

        $this->assertSame(1, $repository->deleteByUser($user, true));
        $this->assertCount(1, $repository->findBy(['user' => $user]));
        $this->assertSame($active->getId(), $repository->findOneBy(['user' => $user])->getId());

        $this->assertSame(1, $repository->deleteByUser($user));
        $this->assertCount(0, $repository->findBy(['user' => $user]));
        $this->assertNotNull($repository->find($other->getId()));
    }

    /**
     * Suppression en cascade avec l'utilisateur et avec le token d'API
     * @return void
     */
    public function testCascadeDelete(): void
    {
        $byUser = $this->createUserSession();
        $byApiToken = $this->createUserSession();
        $ids = [$byUser->getId(), $byApiToken->getId()];

        // Suppression par Doctrine, qui ne connaît pas les sessions : la cascade vient de la base
        $this->em->remove($byUser->getUser());
        $this->em->remove($byApiToken->getApiToken());
        $this->em->flush();
        $this->em->clear();

        foreach ($ids as $id) {
            $this->assertNull($this->getRepository()->find($id));
        }
    }

    /**
     * @return UserSessionRepository
     */
    private function getRepository(): UserSessionRepository
    {
        return $this->em->getRepository(UserSession::class);
    }
}
