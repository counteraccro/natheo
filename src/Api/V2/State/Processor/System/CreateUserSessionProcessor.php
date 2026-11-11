<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Processor qui ouvre une session pour un utilisateur du back-office, limité par IP et email
 */

namespace App\Api\V2\State\Processor\System;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Api\V2\Exception\InvalidGrantException;
use App\Api\V2\Exception\TooManyRequestsException;
use App\Api\V2\Input\System\CreateUserSessionInput;
use App\Api\V2\Mapper\System\UserSessionMapper;
use App\Api\V2\Resource\System\UserSession;
use App\Api\V2\Security\CurrentUserResolver;
use App\Service\Api\System\User\UserSessionService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProcessorInterface<CreateUserSessionInput, UserSession>
 */
final readonly class CreateUserSessionProcessor implements ProcessorInterface
{
    public function __construct(
        private UserSessionService $userSessionService,
        private CurrentUserResolver $currentUserResolver,
        private TranslatorInterface $translator,
    ) {}

    /**
     * @param CreateUserSessionInput $data
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
        /** @var Request $request */
        $request = $context['request'];
        $clientIp = (string) $request->getClientIp();

        $result = $this->userSessionService->authenticate($data->email, $data->password, $clientIp);
        if ($result->isThrottled()) {
            throw new TooManyRequestsException(
                (int) $result->retryAfter,
                $this->translator->trans('api_errors.too.many.requests', domain: 'api_errors'),
            );
        }
        if ($result->user === null) {
            throw new InvalidGrantException(
                $this->translator->trans('api_errors.session.invalid_credentials', domain: 'api_errors'),
            );
        }

        $tokens = $this->userSessionService->create(
            $result->user,
            $this->currentUserResolver->getApiToken(),
            $request->headers->get('User-Agent'),
            $clientIp,
        );
        return (new UserSessionMapper())->mapWithTokens($tokens);
    }
}
