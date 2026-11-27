<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Tests de la révocation des sessions API du site public et du back-office à la déconnexion du back-office
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
     * Déconnexion : sessions API du site public et du back-office révoquées et retirées de la session PHP
     * @return void
     */
    public function testLogoutRevokesSessions(): void
    {
        $userSessionService = $this->container->get(UserSessionService::class);
        $user = $this->createUserContributeur(['disabled' => false, 'anonymous' => false]);
        $frontToken = self::getFaker()->uuid();
        $this->createApiToken(['token' => TokenHasher::hash($frontToken), 'disabled' => false]);
        $phpSession = new Session(new MockArraySessionStorage());
        $accessToken = $userSessionService->getFrontAccessToken($user, $frontToken, $phpSession, null, null);
        $backOffice = $userSessionService->getBackOfficeAccessToken($user, null, $phpSession, null, null);

        $request = new Request();
        $request->setSession($phpSession);
        $this->container->get(UserSessionLogoutListener::class)(new LogoutEvent($request, null));

        $this->assertNull($phpSession->get(UserSessionService::FRONT_SESSION_KEY));
        $this->assertNull($userSessionService->findByAccessToken($accessToken));
        $this->assertNull($phpSession->get(UserSessionService::BACK_OFFICE_SESSION_KEY));
        $this->assertNull($userSessionService->findByAccessToken($backOffice->token));
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
