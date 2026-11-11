<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider des sessions actives de l'utilisateur de la requête
 */

namespace App\Api\V2\State\Provider\System;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Exception\ForbiddenException;
use App\Api\V2\Mapper\System\UserSessionMapper;
use App\Api\V2\Resource\System\UserSession;
use App\Api\V2\Security\CurrentUserResolver;
use App\Service\Api\System\User\UserSessionService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProviderInterface<UserSession>
 */
final readonly class UserSessionCollectionProvider implements ProviderInterface
{
    public function __construct(
        private UserSessionService $userSessionService,
        private CurrentUserResolver $currentUserResolver,
        private TranslatorInterface $translator,
    ) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return UserSession[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $current =
            $this->currentUserResolver->getSession() ??
            throw new ForbiddenException($this->translator->trans('api_errors.session.required', domain: 'api_errors'));

        $mapper = new UserSessionMapper($current->getId());
        return array_map(
            fn($session) => $mapper->map($session),
            $this->userSessionService->findActiveByUser($current->getUser()),
        );
    }
}
