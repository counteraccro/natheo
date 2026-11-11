<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Processor qui renouvelle les tokens d'une session par son refresh token
 */

namespace App\Api\V2\State\Processor\System;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Api\V2\Exception\InvalidGrantException;
use App\Api\V2\Mapper\System\UserSessionMapper;
use App\Api\V2\Resource\System\UserSession;
use App\Api\V2\Resource\System\UserSessionRefresh;
use App\Api\V2\Security\CurrentUserResolver;
use App\Service\Api\System\User\UserSessionService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProcessorInterface<UserSessionRefresh, UserSession>
 */
final readonly class CreateUserSessionRefreshProcessor implements ProcessorInterface
{
    public function __construct(
        private UserSessionService $userSessionService,
        private CurrentUserResolver $currentUserResolver,
        private TranslatorInterface $translator,
    ) {}

    /**
     * @param UserSessionRefresh $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return UserSession
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): UserSession {
        $tokens =
            $this->userSessionService->refresh($data->refreshToken, $this->currentUserResolver->getApiToken()) ??
            throw new InvalidGrantException(
                $this->translator->trans('api_errors.session.invalid_refresh_token', domain: 'api_errors'),
            );
        return (new UserSessionMapper())->mapWithTokens($tokens);
    }
}
