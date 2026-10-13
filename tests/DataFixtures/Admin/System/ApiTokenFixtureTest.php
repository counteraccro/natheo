<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test fixture ApiToken : génération du token du front
 */
namespace App\Tests\DataFixtures\Admin\System;

use App\DataFixtures\Admin\System\ApiTokenFixture;
use App\Entity\Admin\System\ApiToken;
use App\Entity\Admin\System\OptionSystem;
use App\Enum\Admin\System\Options\OptionSystem as OptionSystemEnum;
use App\Tests\AppWebTestCase;
use App\Utils\System\ApiToken\ApiTokenConst;
use App\Utils\System\ApiToken\TokenHasher;
use Doctrine\Common\DataFixtures\ReferenceRepository;

class ApiTokenFixtureTest extends AppWebTestCase
{
    /**
     * Le token du front est généré aléatoirement, en écriture, et enregistré dans l'option OS_FRONT_API_TOKEN
     * @return void
     */
    public function testLoadFrontToken(): void
    {
        $optionKey = OptionSystemEnum::OS_FRONT_API_TOKEN->value;
        $option = $this->em->getRepository(OptionSystem::class)->findOneBy(['key' => $optionKey]);
        $option->setValue('');
        $this->em->flush();

        $referenceRepository = new ReferenceRepository($this->em);
        $referenceRepository->addReference($optionKey, $option);

        /** @var ApiTokenFixture $fixture */
        $fixture = $this->container->get(ApiTokenFixture::class);
        $fixture->setReferenceRepository($referenceRepository);
        $fixture->load($this->em);

        $token = $option->getValue();
        $this->assertMatchesRegularExpression(
            sprintf(
                '/^[A-Za-z0-9]{%1$d}(\.[A-Za-z0-9]{%1$d}){%2$d}$/',
                ApiTokenConst::API_TOKEN_LENGTH,
                ApiTokenConst::API_TOKEN_SEGMENT - 1,
            ),
            $token,
        );
        $this->assertNotContains($token, [
            ApiTokenConst::API_TOKEN_READ,
            ApiTokenConst::API_TOKEN_WRITE,
            ApiTokenConst::API_TOKEN_ADMIN,
        ]);

        $apiToken = $this->em->getRepository(ApiToken::class)->findOneBy(['token' => TokenHasher::hash($token)]);
        $this->assertInstanceOf(ApiToken::class, $apiToken);
        $this->assertFalse($apiToken->isDisabled());
        $this->assertContains(ApiTokenConst::API_TOKEN_ROLE_WRITE, $apiToken->getRoles());
    }
}
