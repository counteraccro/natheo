<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test du UserService
 */
namespace App\Tests\Service\Admin\System;

use App\Entity\Admin\System\User;
use App\Service\Admin\System\User\UserService;
use App\Tests\AppWebTestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class UserServiceTest extends AppWebTestCase
{

    /**
     * @var UserService
     */
    private UserService $userService;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->userService = $this->container->get(UserService::class);
    }

    /**
     * test le listing grid
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetAllFormatToGrid(): void
    {
        $this->createUser();
        $this->createUserContributeur();
        $result = $this->userService->getAllFormatToGrid(1, 10);

        $this->assertArrayHasKey('nb', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('column', $result);
        $this->assertArrayHasKey('sql', $result);
        $this->assertArrayHasKey('translate', $result);
        $this->assertEquals(2, $result['nb']);
        $this->assertCount(2, $result['data']);
    }

    /**
     * Test le listing grid avec recherche
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetAllFormatToGridWitchSearch(): void
    {
        $user = $this->createUser();
        $result = $this->userService->getAllFormatToGrid(1, 10, self::getFaker()->name());
        $this->assertEquals(0, $result['nb']);
        $this->assertCount(0, $result['data']);

        $result = $this->userService->getAllFormatToGrid(1, 10, $user->getLogin());
        $this->assertEquals(1, $result['nb']);
        $this->assertCount(1, $result['data']);
    }

    /**
     * Mise à jour mot de passe
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUpdatePassword(): void
    {
        $user = $this->createUser();
        $password = $user->getPassword();
        $this->userService->updatePassword($user, self::getFaker()->password());

        $userUpdate = $this->userService->findOneById(User::class, $user->getId());
        $this->assertNotNull($userUpdate);
        $this->assertNotEquals($password, $userUpdate->getPassword());
    }
}
