<?php

declare(strict_types=1);

namespace App\DataFixtures\Admin\System;

use App\DataFixtures\AppFixtures;
use App\Entity\Admin\System\ApiToken;
use App\Utils\System\ApiToken\TokenHasher;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Yaml\Yaml;
use App\Service\Admin\System\OptionSystemService;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use App\Enum\Admin\System\Options\OptionSystem;

class ApiTokenFixture extends AppFixtures implements FixtureGroupInterface, OrderedFixtureInterface
{
    const API_TOKEN_FIXTURES_DATA_FILE = 'system' . DIRECTORY_SEPARATOR . 'api_token_fixtures_data.yaml';

    public function __construct(
        #[
            AutowireLocator([
                'container' => ContainerBagInterface::class,
            ]),
        ]
        ContainerInterface $handlers,
        private readonly OptionSystemService $optionSystemService,
    ) {
        parent::__construct($handlers);
    }

    /**
     * @param ObjectManager $manager
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function load(ObjectManager $manager): void
    {
        $debugMode = (bool) $this->container->get('app.debug_mode');

        if (!$debugMode) {
            return;
        }

        $data = Yaml::parseFile($this->pathDataFixtures . self::API_TOKEN_FIXTURES_DATA_FILE);
        foreach ($data['api_token'] as $dataApiToken) {
            $apiToken = new ApiToken();
            foreach ($dataApiToken as $key => $value) {
                if ($key === 'roles') {
                    $apiToken->setRoles([$value]);
                } elseif ($key === 'token') {
                    $apiToken->setToken(TokenHasher::hash($value));
                } else {
                    $this->setData($key, $value, $apiToken);
                }
            }
            $manager->persist($apiToken);
        }

        $token = bin2hex(random_bytes(32));

        $this->optionSystemService->saveValueByKee(OptionSystem::OS_FRONT_API_KEY->value, $token);
        flush();
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
