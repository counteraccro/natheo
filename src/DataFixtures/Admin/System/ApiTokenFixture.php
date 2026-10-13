<?php

declare(strict_types=1);

namespace App\DataFixtures\Admin\System;

use App\DataFixtures\AppFixtures;
use App\Entity\Admin\System\ApiToken;
use App\Entity\Admin\System\OptionSystem;
use App\Enum\Admin\System\Options\OptionSystem as OptionSystemEnum;
use App\Utils\System\ApiToken\TokenGenerator;
use App\Utils\System\ApiToken\TokenHasher;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Yaml\Yaml;

class ApiTokenFixture extends AppFixtures implements FixtureGroupInterface, OrderedFixtureInterface
{
    const API_TOKEN_FIXTURES_DATA_FILE = 'system' . DIRECTORY_SEPARATOR . 'api_token_fixtures_data.yaml';

    /**
     * @param ObjectManager $manager
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function load(ObjectManager $manager): void
    {
        $data = Yaml::parseFile($this->pathDataFixtures . self::API_TOKEN_FIXTURES_DATA_FILE);

        $this->loadFrontToken($data['api_token_front'], $manager);

        // Tokens d'exemple uniquement en mode debug
        if ((bool) $this->container->get('app.debug_mode')) {
            foreach ($data['api_token'] as $dataApiToken) {
                $manager->persist($this->createApiToken($dataApiToken, $dataApiToken['token']));
            }
        }
        $manager->flush();
    }

    /**
     * Crée le token du front avec une valeur aléatoire et l'enregistre dans l'option système OS_FRONT_API_TOKEN
     * @param array $dataApiToken
     * @param ObjectManager $manager
     * @return void
     */
    private function loadFrontToken(array $dataApiToken, ObjectManager $manager): void
    {
        $token = TokenGenerator::generate();
        $manager->persist($this->createApiToken($dataApiToken, $token));

        $optionKey = OptionSystemEnum::OS_FRONT_API_TOKEN->value;
        if ($this->hasReference($optionKey, OptionSystem::class)) {
            $this->getReference($optionKey, OptionSystem::class)->setValue($token);
        }
    }

    /**
     * Construit un ApiToken à partir des données de fixture, seul le hash du token est stocké
     * @param array $dataApiToken
     * @param string $token token en clair
     * @return ApiToken
     */
    private function createApiToken(array $dataApiToken, string $token): ApiToken
    {
        $apiToken = new ApiToken();
        foreach ($dataApiToken as $key => $value) {
            if ($key === 'roles') {
                $apiToken->setRoles([$value]);
            } elseif ($key !== 'token') {
                $this->setData($key, $value, $apiToken);
            }
        }
        $apiToken->setToken(TokenHasher::hash($token));
        return $apiToken;
    }

    /**
     * @return array|string[]
     */
    public static function getGroups(): array
    {
        return [self::GROUP_SYSTEM, self::GROUP_API_TOKEN];
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return 105;
    }
}
