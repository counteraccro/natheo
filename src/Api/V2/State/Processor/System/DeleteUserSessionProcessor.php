<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Processor qui ferme une session de l'utilisateur de la requête
 */

namespace App\Api\V2\State\Processor\System;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Api\V2\Resource\System\UserSession;
use App\Api\V2\Security\CurrentUserResolver;
use App\Service\Api\System\User\UserSessionService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @implements ProcessorInterface<UserSession, null>
 */
final readonly class DeleteUserSessionProcessor implements ProcessorInterface
{
    public function __construct(
        private UserSessionService $userSessionService,
        private CurrentUserResolver $currentUserResolver,
    ) {}

    /**
     * @param UserSession $data session lue par UserSessionProvider, qui a vérifié qu'elle appartient à l'utilisateur
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): null
    {
        $session = $this->userSessionService->findActiveByUserAndId(
            $this->currentUserResolver->requireSession()->getUser(),
            $data->id,
        );
        if ($session !== null) {
            $this->userSessionService->revoke($session);
        }
        return null;
    }
}
