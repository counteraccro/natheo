<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Processor qui authentifie un utilisateur du back-office, limité par IP et email
 */

namespace App\Api\V2\State\Processor\System;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Api\V2\Exception\TooManyRequestsException;
use App\Api\V2\Exception\UnauthorizedException;
use App\Api\V2\Input\System\AuthenticateUserInput;
use App\Api\V2\Resource\System\UserToken;
use App\Service\Admin\System\User\UserDataService;
use App\Service\Admin\System\User\UserService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProcessorInterface<AuthenticateUserInput, UserToken>
 */
final readonly class AuthenticateUserProcessor implements ProcessorInterface
{
    public function __construct(
        private UserService $userService,
        private UserDataService $userDataService,
        private TranslatorInterface $translator,
        #[Autowire(service: 'limiter.api_auth_user')] private RateLimiterFactoryInterface $limiter,
    ) {}

    /**
     * @param AuthenticateUserInput $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return UserToken
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): UserToken
    {
        /** @var Request $request */
        $request = $context['request'];
        $limiter = $this->limiter->create($request->getClientIp() . '-' . mb_strtolower($data->email));
        $limit = $limiter->consume();
        if (!$limit->isAccepted()) {
            throw new TooManyRequestsException(
                max(0, $limit->getRetryAfter()->getTimestamp() - time()),
                $this->translator->trans('api_errors.too.many.requests', domain: 'api_errors'),
            );
        }

        $user = $this->userService->getUserByEmailAndPassword($data->email, $data->password);
        if ($user === null || $user->getRoles() === ['ROLE_USER']) {
            throw new UnauthorizedException(
                $this->translator->trans('api_errors.user.not.found', domain: 'api_errors'),
            );
        }
        $limiter->reset();

        $token = $this->userDataService->generateUserToken($user);
        return new UserToken($token, $this->userDataService->getUserTokenExpiresAt($user));
    }
}
