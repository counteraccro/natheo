<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Fixture pour l'entité User
 */

namespace App\DataFixtures\Admin;

use App\DataFixtures\AppFixtures;
use App\Entity\Admin\User;
use App\Service\Admin\OptionUserService;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Yaml\Yaml;

class UserFixtures extends AppFixtures implements FixtureGroupInterface, OrderedFixtureInterface
{


    const USER_FIXTURES_DATA_FILE = 'user_fixtures_data.yaml';

    /**
     * @var UserPasswordHasherInterface
     */
    private UserPasswordHasherInterface $passwordHasher;

    /**
     * @var OptionUserService
     */
    private OptionUserService $optionUserService;

    /**
     * @param ContainerBagInterface $params
     * @param UserPasswordHasherInterface $passwordHasher
     * @param OptionUserService $optionUserService
     */
    public function __construct(ContainerBagInterface $params, UserPasswordHasherInterface $passwordHasher,
                                OptionUserService     $optionUserService
    )
    {
        $this->passwordHasher = $passwordHasher;
        $this->optionUserService = $optionUserService;
        parent::__construct($params);
    }

    public function load(ObjectManager $manager): void
    {
        $data = Yaml::parseFile($this->pathDataFixtures . self::USER_FIXTURES_DATA_FILE);
        foreach ($data['user'] as $data) {
            $user = new User();
            $exclude = ['roles', 'password'];
            foreach ($data as $key => $value) {
                if (in_array($key, $exclude)) {
                    switch ($key) {
                        case 'roles' :
                            $user->setRoles([$value]);
                            break;
                        case 'password':
                            $user->setPassword($this->passwordHasher->hashPassword($user, $value));
                            break;
                        default:
                    }
                } else {
                    $this->setData($key, $value, $user);
                }

            }
            $user = $this->optionUserService->createOptionsUser($user);
            $manager->persist($user);
        }
        $manager->flush();

        $manager->flush();
    }

    /**
     * Set une valeur pour l'objet User
     * @param string $key
     * @param mixed $value
     * @param User $user
     * @return void
     */
    private function setData(string $key, mixed $value, User $user): void
    {
        $func = 'set' . ucfirst($key);
        $user->$func($value);
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
        return 3; // smaller means sooner
    }

}
