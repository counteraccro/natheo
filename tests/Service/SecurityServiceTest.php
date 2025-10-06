<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 *  Test SecurityService
 */

namespace App\Tests\Service;

use App\Entity\Admin\System\User;
use App\Service\SecurityService;
use App\Tests\AppWebTestCase;
use App\Utils\System\User\UserDataKey;
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
        $optionUser = $this->createUserData($user, [
            'key' => UserDataKey::KEY_RESET_PASSWORD,
            'value' => self::getFaker()->text(),
        ]);
        $result = $this->securityService->canChangePassword($optionUser->getValue());
        $this->assertNotNull($result);
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($user->getLogin(), $result->getLogin());

        $result = $this->securityService->canChangePassword(self::getFaker()->text());
        $this->assertNull($result);
    }
}
