<?php
/**
 * Class global pour les test des API
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace Api\v1;

use App\Utils\Api\Parameters\ApiParametersUserAuthRef;
use App\Utils\Api\Tests\ApiUserAuthenticationTestConst;
use App\Utils\System\ApiToken\ApiTokenConst;
use App\Utils\System\User\Role;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;

class AppApiTest extends WebTestCase
{

    /**
     * Version API
     * @var int
     */
    protected const API_VERSION = 'v1';

    /**
     * URL API pour les tests
     * @var string
     */
    protected const URL_REF = 'http://dev.natheo/api/' . self::API_VERSION;

    /**
     * Code pour générer un header avec un mauvais token
     * @var int
     */
    protected const HEADER_WRONG = 1;

    /**
     * Code pour générer un header avec un token accès lecture
     * @var int
     */
    protected const HEADER_READ = 2;

    /**
     * Code pour générer un header avec un token accès écriture
     * @var int
     */
    protected const HEADER_WRITE = 3;

    /**
     * Code pour générer un header avec un token accès admin
     * @var int
     */
    protected const HEADER_ADMIN = 4;


    /**
     * Point d'entrée pour les tests
     * @return void
     */
    public function testOnline()
    {
        $this->assertTrue(true);
    }

    /**
     * Génère le header pour tester les APIs
     * @param int $typeHeader
     * @return array
     */
    protected function getCustomHeaders(int $typeHeader = self::HEADER_READ): array
    {
        $token = match ($typeHeader) {
            self::HEADER_WRONG => 123,
            self::HEADER_READ => ApiTokenConst::API_TOKEN_READ,
            self::HEADER_WRITE => ApiTokenConst::API_TOKEN_WRITE,
            self::HEADER_ADMIN => ApiTokenConst::API_TOKEN_ADMIN,
            default => '',
        };

        return [
            'HTTP_Accept' => 'application/json',
            'HTTP_Content-Type' => 'application/json',
            'HTTP_Authorization' => 'Bearer ' . $token
        ];

    }

    /**
     * Retourne les informations d'authentification en fonction du role demandé
     * @param array $params
     * @param string $role
     * @return array|string[]
     */
    protected function getUserAuthParams(array $params, string $role = Role::ROLE_CONTRIBUTEUR): array
    {
        if ($role === 'bad_parameter') {
            $userAuth = [
                'username2' => 'username2',
                'password2' => 'password2'
            ];
        } else if ($role === 'bad_type') {
            $userAuth = [
                ApiParametersUserAuthRef::PARAMS_REF_AUTH_USER_USERNAME => 22,
                ApiParametersUserAuthRef::PARAMS_REF_AUTH_USER_PASSWORD => true
            ];
        } else {
            $userAuth = ApiUserAuthenticationTestConst::AUTHENTICATION_USER_TAB[$role];
        }

        return array_merge($params, $userAuth);
    }

    /**
     * Retourne un service en fonction de sa class name
     * @param string $className
     * @return mixed|object|Container|null
     */
    protected function getServiceByClassName(string $className): mixed
    {
        self::bootKernel();
        $container = static::getContainer();
        return $container->get($className);
    }

    /**
     * Retourne le dernier id de l'entité en paramètre
     * @param string $entity
     * @return int
     */
    protected function getLastIdByEntity(string $entity): int
    {
        self::bootKernel();
        $manager = static::getContainer()
            ->get('doctrine')
            ->getManager();
        $row = $manager->getRepository($entity)->findOneBy([], ['id' => 'DESC']);
        return $row ? $row->getId() : 0;
    }
}