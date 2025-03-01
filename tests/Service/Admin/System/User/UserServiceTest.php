<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test du UserService
 */

namespace App\Tests\Service\Admin\System\User;

use App\Entity\Admin\System\User;
use App\Service\Admin\System\User\UserService;
use App\Tests\AppWebTestCase;
use App\Utils\System\User\Anonymous;
use App\Utils\System\User\Role;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;

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
        $user = $this->createUser();
        $this->createUserContributeur();
        $result = $this->userService->getAllFormatToGrid(1, 10);

        $this->assertArrayHasKey('nb', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('column', $result);
        $this->assertArrayHasKey('sql', $result);
        $this->assertArrayHasKey('translate', $result);
        $this->assertEquals(2, $result['nb']);
        $this->assertCount(2, $result['data']);

        $result = $this->userService->getAllFormatToGrid(1, 10, $user->getLogin());
        $this->assertCount(1, $result['data']);
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

    /**
     * Test anonymisation
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAnonymizer(): void
    {
        $user = $this->createUser();

        $email = $user->getEmail();
        $password = $user->getPassword();

        $this->userService->anonymizer($user);
        /** @var User $user */
        $user = $this->userService->findOneById(User::class, $user->getId());

        $this->assertEquals(Anonymous::FIRST_NAME, $user->getFirstName());
        $this->assertEquals(Anonymous::LAST_NAME, $user->getLastName());
        $this->assertEquals(Anonymous::LOGIN, $user->getLogin());
        $this->assertNotEquals($password, $user->getPassword());
        $this->assertNotEquals($email, $user->getPassword());
        $this->assertTrue($user->isDisabled());
        $this->assertTrue($user->isAnonymous());
        $this->assertFalse($user->isFounder());
        $this->assertCount(0, $user->getOptionsUser()->toArray());

    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetByRole(): void
    {
        $user = $this->createUserFounder();
        $result = $this->userService->getByRole(Role::ROLE_CONTRIBUTEUR);
        $this->assertCount(1, $result);
    }

    /**
     * Retourne une liste d'email en fonction d'un user
     * @return void
     */
    public function testGetTabMailByListeUser(): void
    {
        $array = [
            $user = $this->createUser(),
            $user2 = $this->createUserFounder(),
            $user3 = $this->createUserContributeur(),
        ];

        $result = $this->userService->getTabMailByListeUser($array);
        $this->assertCount(3, $result);
        $this->assertEquals($user->getEmail(), $result[0]);
        $this->assertEquals($user2->getEmail(), $result[1]);
        $this->assertEquals($user3->getEmail(), $result[2]);
    }

    /**
     * Créer un nouvel user
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAddUser(): void
    {
        $user = $this->createUser([], false);

        $user->setLogin(null);
        $user = $this->userService->addUser($user);

        $this->assertNotNull($user->getLogin());

        $result = $this->userService->findOneById(User::class, $user->getId());
        $this->assertNotNull($result);
    }

    /**
     * Récupération d'un utilisateur en fonction de son login/password
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetUserByEmailAndPassword(): void
    {
        $factory = new PasswordHasherFactory([
            'common' => ['algorithm' => 'auto']
        ]);
        $hasher = $factory->getPasswordHasher('common');

        $password = self::getFaker()->password();
        $user = $this->createUser(['disabled' => false, 'anonymous' => false, 'password' => $hasher->hash($password)]);

        $result = $this->userService->getUserByEmailAndPassword($user->getEmail(), $password);
        $this->assertNotNull($result);

        $result = $this->userService->getUserByEmailAndPassword(self::getFaker()->email(), $password);
        $this->assertNull($result);

        $result = $this->userService->getUserByEmailAndPassword($user->getEmail(), self::getFaker()->password());
        $this->assertNull($result);

        $password = self::getFaker()->password();
        $user = $this->createUser(['disabled' => true, 'anonymous' => true, 'password' => $hasher->hash($password)]);
        $result = $this->userService->getUserByEmailAndPassword($user->getEmail(), $password);
        $this->assertNull($result);

    }
}
