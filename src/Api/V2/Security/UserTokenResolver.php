<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Utilisateur du back-office désigné par l'en-tête X-User-Token, en plus du token de l'API
 */

namespace App\Api\V2\Security;

use App\Api\V2\Exception\ForbiddenException;
use App\Entity\Admin\System\User;
use App\Service\Api\System\User\ApiUserService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class UserTokenResolver
{
    /**
     * Nom de l'en-tête déclaré sur les opérations
     * @var string
     */
    public const string HEADER = 'X-User-Token';

    /**
     * Description de l'en-tête dans l'OpenAPI
     * @var string
     */
    public const string HEADER_DESCRIPTION = 'Token de l\'utilisateur du back-office, 403 s\'il est invalide ou expiré';

    public function __construct(
        private RequestStack $requestStack,
        private ApiUserService $apiUserService,
        private TranslatorInterface $translator,
    ) {}

    /**
     * Utilisateur de l'en-tête, null si l'en-tête est absent
     * @return User|null
     * @throws ForbiddenException token invalide ou expiré
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getUser(): ?User
    {
        $token = $this->requestStack->getCurrentRequest()?->headers->get(self::HEADER);
        if ($token === null || $token === '') {
            return null;
        }

        return $this->apiUserService->getUserByUserToken($token) ??
            throw new ForbiddenException(
                $this->translator->trans('api_errors.user.token.not.found', domain: 'api_errors'),
            );
    }
}
