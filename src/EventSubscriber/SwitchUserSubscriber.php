<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Contrôle et trace la prise de contrôle d'un compte (switch_user)
 */
namespace App\EventSubscriber;

use App\Entity\Admin\System\User;
use App\Service\Admin\System\User\UserService;
use App\Service\LoggerService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class SwitchUserSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly UserService $userService,
        private readonly LoggerService $loggerService,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [SecurityEvents::SWITCH_USER => 'onSwitchUser'];
    }

    /**
     * Refuse la prise de contrôle d'un compte non gérable par l'utilisateur d'origine
     * (fondateur, super admin si l'utilisateur n'est pas fondateur) puis log l'action
     * @param SwitchUserEvent $event
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function onSwitchUser(SwitchUserEvent $event): void
    {
        $token = $event->getToken();

        // Retour au compte d'origine (_exit)
        if (!($token instanceof SwitchUserToken)) {
            return;
        }

        $originalUser = $token->getOriginalToken()->getUser();
        $targetUser = $event->getTargetUser();

        if (
            !($originalUser instanceof User) ||
            !($targetUser instanceof User) ||
            !$this->userService->canManage($targetUser, $originalUser)
        ) {
            throw new AccessDeniedException('Switch user not allowed.');
        }

        $this->loggerService->logSwitchUser($originalUser->getEmail(), $targetUser->getEmail());
    }
}
