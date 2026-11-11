<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider d'une session active de l'utilisateur de la requête, current désignant celle de la requête
 */

namespace App\Api\V2\State\Provider\System;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Exception\NotFoundException;
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
final readonly class UserSessionProvider implements ProviderInterface
{
    /**
     * Identifiant de la session de la requête dans l'URL
     * @var string
     */
    public const string CURRENT = 'current';

    public function __construct(
        private UserSessionService $userSessionService,
        private CurrentUserResolver $currentUserResolver,
        private TranslatorInterface $translator,
    ) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return UserSession
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): UserSession
    {
        $current = $this->currentUserResolver->requireSession();

        // API Platform convertit l'identifiant en entier : current est relu dans la route
        $id = (string) ($context['request']?->attributes->get('_route_params')['id'] ?? $uriVariables['id']);
        $session =
            $id === self::CURRENT
                ? $current
                : $this->userSessionService->findActiveByUserAndId($current->getUser(), (int) $id);
        if ($session === null) {
            throw new NotFoundException(
                $this->translator->trans('api_errors.find.session.not.found', domain: 'api_errors'),
            );
        }
        return (new UserSessionMapper($current->getId()))->map($session);
    }
}
