<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test de la lecture de l'utilisateur, de la session et du token d'API de la requête
 */

namespace App\Tests\Api\V2\Security;

use App\Api\V2\Exception\ForbiddenException;
use App\Api\V2\Security\CurrentUserResolver;
use App\Enum\Admin\System\ApiToken\ApiTokenScope;
use App\Security\ApiUser;
use App\Tests\AppWebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

class CurrentUserResolverTest extends AppWebTestCase
{
    /**
     * Un access token de session donne la session, son utilisateur et le token d'API de la session
     * @return void
     */
    public function testSession(): void
    {
        $user = $this->createUserContributeur();
        $apiToken = $this->createApiToken(['scope' => ApiTokenScope::BACK_OFFICE, 'disabled' => false]);
        $session = $this->createUserSession(['user' => $user, 'apiToken' => $apiToken]);
        $resolver = $this->authenticate(new ApiUser($apiToken, $session));

        $this->assertSame($session, $resolver->getSession());
        $this->assertSame($session, $resolver->requireSession());
        $this->assertSame($user, $resolver->getUser());
        $this->assertSame($apiToken, $resolver->getApiToken());
    }

    /**
     * Un token d'API seul donne le token, sans session ni utilisateur
     * @return void
     */
    public function testApiTokenOnly(): void
    {
        $apiToken = $this->createApiToken(['disabled' => false]);
        $resolver = $this->authenticate(new ApiUser($apiToken));

        $this->assertNull($resolver->getSession());
        $this->assertNull($resolver->getUser());
        $this->assertSame($apiToken, $resolver->getApiToken());
        $this->expectException(ForbiddenException::class);
        $resolver->requireSession();
    }

    /**
     * Un utilisateur du back-office ou un anonyme n'est pas un appel à l'API
     * @return void
     */
    public function testOutsideApi(): void
    {
        foreach ([$this->createUserAdmin(), null] as $user) {
            $resolver = $this->authenticate($user);
            $this->assertNull($resolver->getSession());
            $this->assertNull($resolver->getUser());
            $this->assertNull($resolver->getApiToken());
        }
    }

    /**
     * Place l'utilisateur dans le token de sécurité, puis rend le résolveur
     * @param UserInterface|null $user
     * @return CurrentUserResolver
     */
    private function authenticate(?UserInterface $user): CurrentUserResolver
    {
        $this->container
            ->get(TokenStorageInterface::class)
            ->setToken($user === null ? null : new UsernamePasswordToken($user, 'api', $user->getRoles()));
        return $this->container->get(CurrentUserResolver::class);
    }
}
