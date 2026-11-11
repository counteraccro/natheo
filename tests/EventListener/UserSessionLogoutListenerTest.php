<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de la révocation de la session API du site public à la déconnexion du back-office
 */

namespace App\Tests\EventListener;

use App\EventListener\UserSessionLogoutListener;
use App\Service\Api\System\User\UserSessionService;
use App\Service\Global\Token\TokenHasher;
use App\Tests\AppWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Security\Http\EventListener\SessionLogoutListener;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UserSessionLogoutListenerTest extends AppWebTestCase
{
    /**
     * Déconnexion : session API du site public révoquée et retirée de la session PHP
     * @return void
     */
    public function testLogoutRevokesFrontSession(): void
    {
        $userSessionService = $this->container->get(UserSessionService::class);
        $frontToken = self::getFaker()->uuid();
        $this->createApiToken(['token' => TokenHasher::hash($frontToken), 'disabled' => false]);
        $phpSession = new Session(new MockArraySessionStorage());
        $accessToken = $userSessionService->getFrontAccessToken(
            $this->createUserContributeur(['disabled' => false, 'anonymous' => false]),
            $frontToken,
            $phpSession,
            null,
            null,
        );

        $request = new Request();
        $request->setSession($phpSession);
        $this->container->get(UserSessionLogoutListener::class)(new LogoutEvent($request, null));

        $this->assertNull($phpSession->get(UserSessionService::FRONT_SESSION_KEY));
        $this->assertNull($userSessionService->findByAccessToken($accessToken));
    }

    /**
     * Le listener passe avant SessionLogoutListener, qui vide la session PHP où est gardée la session API
     * @return void
     */
    public function testListenerRunsBeforeSessionInvalidation(): void
    {
        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->container->get('security.event_dispatcher.admin');

        $classes = array_map(
            fn($listener) => is_array($listener) ? $listener[0]::class : $listener::class,
            $dispatcher->getListeners(LogoutEvent::class),
        );
        $listenerIndex = array_search(UserSessionLogoutListener::class, $classes, true);
        $sessionIndex = array_search(SessionLogoutListener::class, $classes, true);

        $this->assertIsInt($listenerIndex);
        $this->assertIsInt($sessionIndex);
        $this->assertLessThan($sessionIndex, $listenerIndex);
    }
}
