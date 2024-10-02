<?php
/**
 * Class global pour les test des API
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace Api\v1;

use App\Utils\System\ApiToken\ApiTokenConst;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AppApiTest extends WebTestCase
{

    /**
     * Version API
     * @var int
     */
    protected const API_VERSION = 1;

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
            'HTTP_Content-Type' => 'application/json',
            'HTTP_Authorization' => 'Bearer ' . $token
        ];

    }
}