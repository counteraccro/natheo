<?php

namespace Api;

use App\Utils\System\ApiToken\ApiTokenConst;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use function Symfony\Component\String\b;

class AppApiTest extends WebTestCase
{

    protected const API_VERSION = '1';
    protected const URL_REF = 'http://dev.natheo/api/' . self::API_VERSION;

    protected const HEADER_WRONG = 1;
    protected const HEADER_READ = 2;
    protected const HEADER_WRITE = 3;
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