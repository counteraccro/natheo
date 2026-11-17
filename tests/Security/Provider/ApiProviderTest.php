<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test du provider du pare-feu API
 */

namespace App\Tests\Security\Provider;

use App\Security\ApiUser;
use App\Security\Provider\ApiProvider;
use App\Service\Global\Token\TokenHasher;
use App\Tests\AppWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class ApiProviderTest extends AppWebTestCase
{
    private ApiProvider $provider;

    public function setUp(): void
    {
        parent::setUp();
        // Le journal des écritures Doctrine lit la session de la requête
        $request = Request::create('/api/v2/info');
        $request->setSession(new Session(new MockArraySessionStorage()));
        $this->container->get(RequestStack::class)->push($request);
        $this->provider = $this->container->get(ApiProvider::class);
    }

    /**
     * Un token d'API donne un ApiUser sans session, un access token un ApiUser avec l'utilisateur de la session
     * @return void
     */
    public function testLoadUserByIdentifier(): void
    {
        $token = self::getFaker()->uuid();
        $apiToken = $this->createApiToken(['token' => TokenHasher::hash($token), 'disabled' => false]);
        $apiUser = $this->provider->loadUserByIdentifier($token);
        $this->assertInstanceOf(ApiUser::class, $apiUser);
        $this->assertSame($apiToken->getId(), $apiUser->getApiToken()->getId());
        $this->assertNull($apiUser->getUser());

        $user = $this->createUserContributeur(['disabled' => false, 'anonymous' => false]);
        $accessToken = self::getFaker()->uuid();
        $this->createUserSession(['user' => $user, 'accessTokenHash' => TokenHasher::hash($accessToken)]);
        $apiUser = $this->provider->loadUserByIdentifier($accessToken);
        $this->assertSame($user->getId(), $apiUser->getUser()?->getId());
        $this->assertContains('ROLE_CONTRIBUTEUR', $apiUser->getRoles());

        $this->assertTrue($this->provider->supportsClass(ApiUser::class));

        $this->expectException(UserNotFoundException::class);
        $this->provider->loadUserByIdentifier('inconnu');
    }
}
