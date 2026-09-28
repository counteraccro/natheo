<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 *  Test SecurityService
 */

namespace App\Tests\Service;

use App\Entity\Admin\System\User;
use App\Service\Admin\System\User\UserDataService;
use App\Service\SecurityService;
use App\Tests\AppWebTestCase;
use App\Enum\Admin\System\User\UserDataKey;
use Symfony\Component\DependencyInjection\Container;

class SecurityServiceTest extends AppWebTestCase
{
    /**
     * @var mixed|SecurityService|Container|null
     */
    private SecurityService $securityService;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->securityService = $this->container->get(SecurityService::class);
    }

    /**
     * Test méthode canChangePassword()
     * @return void
     * @throws \DateMalformedIntervalStringException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function testCanChangePassword(): void
    {
        $user = $this->createUser();
        $key = self::getFaker()->text();
        $optionUser = $this->createUserData($user, [
            'key' => UserDataKey::RESET_PASSWORD->value,
            'value' => UserDataService::hashResetPasswordKey($key),
        ]);

        // Seule la clé en clair est acceptée, pas son hash stocké en base
        $this->assertNull($this->securityService->canChangePassword($optionUser->getValue()));

        $result = $this->securityService->canChangePassword($key);
        $this->assertNotNull($result);
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($user->getLogin(), $result->getLogin());

        $result = $this->securityService->canChangePassword(self::getFaker()->text());
        $this->assertNull($result);
    }
}
