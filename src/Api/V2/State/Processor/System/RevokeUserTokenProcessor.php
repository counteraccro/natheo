<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Processor qui révoque le token de l'utilisateur du back-office
 */

namespace App\Api\V2\State\Processor\System;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Api\V2\Exception\ForbiddenException;
use App\Api\V2\Security\UserTokenResolver;
use App\Service\Admin\System\User\UserDataService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProcessorInterface<null, null>
 */
final readonly class RevokeUserTokenProcessor implements ProcessorInterface
{
    public function __construct(
        private UserTokenResolver $userTokenResolver,
        private UserDataService $userDataService,
        private TranslatorInterface $translator,
    ) {}

    /**
     * @param mixed $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): null
    {
        $user =
            $this->userTokenResolver->getUser() ??
            throw new ForbiddenException(
                $this->translator->trans('api_errors.user.token.not.found', domain: 'api_errors'),
            );
        $this->userDataService->removeUserToken($user);
        return null;
    }
}
