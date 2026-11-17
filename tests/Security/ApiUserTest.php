<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test de l'utilisateur de sécurité du pare-feu API
 */

namespace App\Tests\Security;

use App\Enum\Admin\System\ApiToken\ApiTokenScope;
use App\Security\ApiUser;
use App\Tests\AppWebTestCase;

class ApiUserTest extends AppWebTestCase
{
    /**
     * Token seul : rôles du token et de son type, aucun utilisateur
     * @return void
     */
    public function testTokenOnly(): void
    {
        $apiToken = $this->createApiToken(['roles' => ['ROLE_WRITE_API'], 'disabled' => false]);
        $apiUser = new ApiUser($apiToken);

        $this->assertNull($apiUser->getUser());
        $this->assertNull($apiUser->getSession());
        $this->assertSame($apiToken, $apiUser->getApiToken());
        $this->assertEqualsCanonicalizing(['ROLE_WRITE_API', 'ROLE_READ_API', 'ROLE_API_PUBLIC'], $apiUser->getRoles());
        $this->assertSame('api_token_' . $apiToken->getId(), $apiUser->getUserIdentifier());
    }

    /**
     * Session : rôles du token, de son type, de la session et vrais rôles de l'utilisateur
     * @return void
     */
    public function testWithSession(): void
    {
        $user = $this->createUserAdmin(['disabled' => false, 'anonymous' => false]);
        $apiToken = $this->createApiToken([
            'roles' => ['ROLE_ADMIN_API'],
            'scope' => ApiTokenScope::BACK_OFFICE,
            'disabled' => false,
        ]);
        $session = $this->createUserSession(['user' => $user, 'apiToken' => $apiToken]);
        $apiUser = new ApiUser($apiToken, $session);

        $this->assertSame($user, $apiUser->getUser());
        $this->assertSame($session, $apiUser->getSession());
        $roles = $apiUser->getRoles();
        foreach (['ROLE_ADMIN_API', 'ROLE_API_BACK_OFFICE', ApiUser::ROLE_USER_SESSION, 'ROLE_ADMIN'] as $role) {
            $this->assertContains($role, $roles);
        }
        $this->assertNotContains('ROLE_API_PUBLIC', $roles);
        $this->assertSame(count($roles), count(array_unique($roles)));
        $this->assertSame($user->getUserIdentifier(), $apiUser->getUserIdentifier());
    }
}
