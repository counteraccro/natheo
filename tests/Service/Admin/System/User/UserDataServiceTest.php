<?php

declare(strict_types=1);
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
use App\Enum\Admin\System\Options\OptionSystem;
use App\Enum\Admin\System\User\UserDataKey;
use App\Utils\System\ApiToken\TokenHasher;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class UserDataServiceTest extends AppWebTestCase
{
    /**
     * @var UserDataService
     */
    private UserDataService $userDataService;

    /**
     * @var OptionSystemService
     */
    private OptionSystemService $optionSystemService;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->userDataService = $this->container->get(UserDataService::class);
        $this->optionSystemService = $this->container->get(OptionSystemService::class);
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
        $this->userDataService->update(UserDataKey::RESET_PASSWORD->value, $text, $user);
        $userData = $this->userDataService->findKeyAndUser(UserDataKey::RESET_PASSWORD->value, $user);

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
        $this->userDataService->update(UserDataKey::RESET_PASSWORD->value, $text, $user);

        $userData = $this->userDataService->findKeyAndValue(UserDataKey::RESET_PASSWORD->value, $text);

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
        $this->createUserData($user, ['key' => UserDataKey::LAST_CONNEXION->value]);
        $time = time();
        $this->userDataService->update(UserDataKey::LAST_CONNEXION->value, strval($time), $user);
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
        $this->createUserData($user, ['key' => UserDataKey::HELP_FIRST_CONNEXION->value]);
        $this->userDataService->update(UserDataKey::HELP_FIRST_CONNEXION->value, '1', $user);
        $this->assertTrue($this->userDataService->getHelpFirstConnexion($user));

        $this->userDataService->update(UserDataKey::HELP_FIRST_CONNEXION->value, '0', $user);
        $this->assertFalse($this->userDataService->getHelpFirstConnexion($user));
    }

    /**
     * Test méthode generateUserToken()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGenerateUserToken(): void
    {
        $user = $this->createUser();
        $token = $this->userDataService->generateUserToken($user);

        $userDataToken = $this->userDataService->findKeyAndUser(UserDataKey::TOKEN_CONNEXION->value, $user);
        $userDataTime = $this->userDataService->findKeyAndUser(UserDataKey::TIME_VALIDATE_TOKEN->value, $user);

        $this->assertNotNull($userDataToken);
        $this->assertNotNull($userDataTime);
        $this->assertEquals(TokenHasher::hash($token), $userDataToken->getValue());
        $this->assertLessThanOrEqual(time() + 60 * 60, intval($userDataTime->getValue()));

        // Ancienne valeur "sans limite" : la validité est plafonnée
        $this->optionSystemService->saveValueByKee(OptionSystem::OS_API_TIME_VALIDATE_USER_TOKEN->value, '-1');
        $this->userDataService->generateUserToken($user);
        $userDataTime = $this->userDataService->findKeyAndUser(UserDataKey::TIME_VALIDATE_TOKEN->value, $user);
        $this->assertLessThanOrEqual(
            time() + UserDataService::USER_TOKEN_MAX_VALIDITY * 60,
            intval($userDataTime->getValue()),
        );
    }

    /**
     * Test méthode removeUserToken()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testRemoveUserToken(): void
    {
        $user = $this->createUser();
        $this->userDataService->generateUserToken($user);
        $this->userDataService->removeUserToken($user);

        $this->assertNull($this->userDataService->findKeyAndUser(UserDataKey::TOKEN_CONNEXION->value, $user));
        $this->assertNull($this->userDataService->findKeyAndUser(UserDataKey::TIME_VALIDATE_TOKEN->value, $user));
    }
}
