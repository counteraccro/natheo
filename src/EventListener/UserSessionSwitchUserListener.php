<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Révoque les sessions API du site public et du back-office à l'entrée et à la sortie d'une usurpation : la page
 * suivante en ouvre une au nom du nouvel utilisateur
 */

namespace App\EventListener;

use App\Service\Api\System\User\UserSessionService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\Security\Http\SecurityEvents;

// Le pare-feu envoie l'événement sous ce nom, pas sous celui de sa classe
#[AsEventListener(event: SecurityEvents::SWITCH_USER)]
final readonly class UserSessionSwitchUserListener
{
    public function __construct(private UserSessionService $userSessionService) {}

    /**
     * @param SwitchUserEvent $event
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(SwitchUserEvent $event): void
    {
        $request = $event->getRequest();
        if (!$request->hasSession()) {
            return;
        }
        $this->userSessionService->revokeFrontSession($request->getSession());
        $this->userSessionService->revokeBackOfficeSession($request->getSession());
    }
}
