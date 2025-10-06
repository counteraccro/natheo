<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Fixture pour l'entité User
 */

namespace App\DataFixtures\Admin\System;

use App\DataFixtures\AppFixtures;
use App\Entity\Admin\System\User;
use App\Service\Admin\NotificationService;
use App\Service\Admin\System\OptionUserService;
use App\Utils\Notification\NotificationKey;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Yaml\Yaml;

class UserFixtures extends AppFixtures implements FixtureGroupInterface, OrderedFixtureInterface
{
    const USER_FIXTURES_DATA_FILE = 'system' . DIRECTORY_SEPARATOR . 'user_fixtures_data.yaml';
    const USER_FIXTURES_DATA_FILE_DEMO = 'system' . DIRECTORY_SEPARATOR . 'user_demo_fixtures_data.yaml';

    /**
     * @var UserPasswordHasherInterface
     */
    private UserPasswordHasherInterface $passwordHasher;

    /**
     * @var OptionUserService
     */
    private OptionUserService $optionUserService;

    /**
     * @var NotificationService|mixed
     */
    private NotificationService $notificationService;

    private ContainerBagInterface $containerBag;

    /**
     * @param ContainerInterface $handlers
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        #[
            AutowireLocator([
                'container' => ContainerBagInterface::class,
                'passwordHasher' => UserPasswordHasherInterface::class,
                'optionUserService' => OptionUserService::class,
                'notificationService' => NotificationService::class,
            ]),
        ]
        private readonly ContainerInterface $handlers,
    ) {
        $this->passwordHasher = $this->handlers->get('passwordHasher');
        $this->optionUserService = $this->handlers->get('optionUserService');
        $this->notificationService = $this->handlers->get('notificationService');
        $this->containerBag = $this->handlers->get('container');
        parent::__construct($this->handlers);
    }

    /**
     * @param ObjectManager $manager
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function load(ObjectManager $manager): void
    {
        $debugMode = (bool) $this->containerBag->get('app.debug_mode');

        if ($debugMode) {
            $data = Yaml::parseFile($this->pathDataFixtures . self::USER_FIXTURES_DATA_FILE_DEMO);
        } else {
            $data = Yaml::parseFile($this->pathDataFixtures . self::USER_FIXTURES_DATA_FILE);
        }

        foreach ($data['user'] as $ref => $data) {
            $user = new User();
            $exclude = ['roles', 'password'];
            foreach ($data as $key => $value) {
                if (in_array($key, $exclude)) {
                    switch ($key) {
                        case 'roles':
                            $user->setRoles([$value]);
                            break;
                        case 'password':
                            if ($debugMode) {
                                $value = $this->passwordHasher->hashPassword($user, $value);
                            }
                            $user->setPassword($value);
                            break;
                        default:
                    }
                } else {
                    $this->setData($key, $value, $user);
                }
            }
            $user = $this->optionUserService->createOptionsUser($user);
            $user = $this->notificationService->addForFixture($user, NotificationKey::NOTIFICATION_WELCOME, [
                'login' => $user->getLogin(),
                'role' => $user->getRoles()[0],
            ]);
            $manager->persist($user);
            $this->addReference($ref, $user);
        }
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return [self::GROUP_REGISTERED, self::GROUP_USER];
    }

    /**
     * Définition de l'ordre
     * @return int
     */
    public function getOrder(): int
    {
        return 103; // smaller means sooner
    }
}
