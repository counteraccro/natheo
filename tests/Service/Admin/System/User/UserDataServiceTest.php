<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test du UserDataService
 */

namespace App\Tests\Service\Admin\System\User;

use App\Entity\Admin\System\User;
use App\Service\Admin\System\OptionSystemService;
use App\Service\Admin\System\User\UserDataService;
use App\Tests\AppWebTestCase;
use App\Utils\System\Options\OptionSystemKey;
use App\Utils\System\User\UserDataKey;
use DateMalformedStringException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class UserDataServiceTest extends AppWebTestCase
{
    /**
     * @var UserDataService
     */
    private UserDataService $userDataService;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->userDataService = $this->container->get(UserDataService::class);
    }

    /**
     * Test update et findKeyAndUser
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUpdate()
    {
        $user = $this->createUser();
        $text = self::getFaker()->text(15);
        $this->userDataService->update(UserDataKey::KEY_RESET_PASSWORD, $text, $user);
        $userData = $this->userDataService->findKeyAndUser(UserDataKey::KEY_RESET_PASSWORD, $user);

        /** @var User $userCheck */
        $this->assertEquals($text, $userData->getValue());
    }

    /**
     * Test méthode findKeyAndValue()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testFindKeyAndValue(): void
    {
        $user = $this->createUser();
        $text = self::getFaker()->text(15);
        $this->userDataService->update(UserDataKey::KEY_RESET_PASSWORD, $text, $user);

        $userData = $this->userDataService->findKeyAndValue(UserDataKey::KEY_RESET_PASSWORD, $text);

        /** @var User $userCheck */
        $this->assertEquals($text, $userData->getValue());
    }

    /**
     * Test méthode getLastConnexion()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetLastConnexion(): void
    {
        $user = $this->createUser();
        $this->createUserData($user, ['key' => UserDataKey::KEY_LAST_CONNEXION]);
        $time = time();
        $this->userDataService->update(UserDataKey::KEY_LAST_CONNEXION, $time, $user);
        $dateTime = $this->userDataService->getLastConnexion($user);
        $this->assertEquals($time, $dateTime->getTimestamp());
    }

    /**
     * Test méthode getHelpFirstConnexion()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetHelpFirstConnexion(): void
    {
        $user = $this->createUser();
        $this->createUserData($user, ['key' => UserDataKey::KEY_HELP_FIRST_CONNEXION]);
        $this->userDataService->update(UserDataKey::KEY_HELP_FIRST_CONNEXION, 1, $user);
        $this->assertTrue($this->userDataService->getHelpFirstConnexion($user));

        $this->userDataService->update(UserDataKey::KEY_HELP_FIRST_CONNEXION, 0, $user);
        $this->assertFalse($this->userDataService->getHelpFirstConnexion($user));
    }

    /**
     * Test méthode generateUserToken()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws DateMalformedStringException
     */
    public function testGenerateUserToken(): void
    {
        $user = $this->createUser();
        $this->userDataService->generateUserToken($user);

        $userDataToken = $this->userDataService->findKeyAndUser(UserDataKey::KEY_TOKEN_CONNEXION, $user);
        $userDataTime = $this->userDataService->findKeyAndUser(UserDataKey::TIME_VALIDATE_TOKEN, $user);

        $this->assertNotNull($userDataToken);
        $this->assertNotNull($userDataTime);
    }
}
