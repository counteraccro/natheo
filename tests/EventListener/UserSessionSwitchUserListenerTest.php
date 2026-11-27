<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de la révocation des sessions API du site public et du back-office à l'entrée et à la sortie d'une usurpation
 */

namespace App\Tests\EventListener;

use App\EventListener\UserSessionSwitchUserListener;
use App\Service\Api\System\User\UserSessionService;
use App\Service\Global\Token\TokenHasher;
use App\Tests\AppWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UserSessionSwitchUserListenerTest extends AppWebTestCase
{
    /**
     * Changement d'utilisateur : sessions API de l'utilisateur précédent révoquées et retirées de la session PHP
     * @return void
     */
    public function testSwitchUserRevokesSessions(): void
    {
        $userSessionService = $this->container->get(UserSessionService::class);
        $admin = $this->createUserContributeur(['disabled' => false, 'anonymous' => false]);
        $frontToken = self::getFaker()->uuid();
        $this->createApiToken(['token' => TokenHasher::hash($frontToken), 'disabled' => false]);
        $phpSession = new Session(new MockArraySessionStorage());
        $front = $userSessionService->getFrontAccessToken($admin, $frontToken, $phpSession, null, null);
        $backOffice = $userSessionService->getBackOfficeAccessToken($admin, null, $phpSession, null, null);

        $request = new Request();
        $request->setSession($phpSession);
        $target = $this->createUserContributeur(['disabled' => false, 'anonymous' => false]);
        $this->container->get(UserSessionSwitchUserListener::class)(new SwitchUserEvent($request, $target));

        $this->assertNull($phpSession->get(UserSessionService::FRONT_SESSION_KEY));
        $this->assertNull($phpSession->get(UserSessionService::BACK_OFFICE_SESSION_KEY));
        $this->assertNull($userSessionService->findByAccessToken($front));
        $this->assertNull($userSessionService->findByAccessToken($backOffice->token));
    }

    /**
     * Le listener écoute le nom d'événement et le répartiteur global utilisés par le pare-feu
     * @return void
     */
    public function testListenerIsRegistered(): void
    {
        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->container->get('event_dispatcher');

        $classes = array_map(
            fn($listener) => is_array($listener) ? $listener[0]::class : $listener::class,
            $dispatcher->getListeners(SecurityEvents::SWITCH_USER),
        );
        $this->assertContains(UserSessionSwitchUserListener::class, $classes);
    }
}
