<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Révoque les sessions API du site public et du back-office quand l'utilisateur se déconnecte du back-office
 */

namespace App\EventListener;

use App\Service\Api\System\User\UserSessionService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LogoutEvent;

#[AsEventListener(event: LogoutEvent::class, priority: 1, dispatcher: 'security.event_dispatcher.admin')]
final readonly class UserSessionLogoutListener
{
    public function __construct(private UserSessionService $userSessionService) {}

    /**
     * @param LogoutEvent $event
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(LogoutEvent $event): void
    {
        $request = $event->getRequest();
        if ($request->hasSession()) {
            $this->userSessionService->revokeFrontSession($request->getSession());
            $this->userSessionService->revokeBackOfficeSession($request->getSession());
        }
    }
}
