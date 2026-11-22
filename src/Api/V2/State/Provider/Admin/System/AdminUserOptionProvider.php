<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Provider d'une option de l'utilisateur de la session
 */

namespace App\Api\V2\State\Provider\Admin\System;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Exception\ForbiddenException;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Mapper\Admin\System\AdminUserOptionMapper;
use App\Api\V2\Resource\Admin\System\AdminUserOption;
use App\Api\V2\Security\CurrentUserResolver;
use App\Entity\Admin\System\User;
use App\Service\Api\Admin\System\ApiAdminUserOptionService;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProviderInterface<AdminUserOption>
 */
final readonly class AdminUserOptionProvider implements ProviderInterface
{
    public function __construct(
        private ApiAdminUserOptionService $userOptionService,
        private CurrentUserResolver $currentUserResolver,
        private TranslatorInterface $translator,
    ) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return AdminUserOption
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AdminUserOption
    {
        $user = $this->getUser();
        [$key, $config] =
            $this->userOptionService->findConfig((string) ($uriVariables['id'] ?? '')) ??
            throw new NotFoundException($this->translator->trans('user.option.error.not_found', domain: 'user'));
        return $this->map($user, $key, $config);
    }

    /**
     * Ressource d'une option avec la valeur enregistrée de l'utilisateur, partagée avec le processor
     * @param User $user
     * @param string $key
     * @param array $config
     * @return AdminUserOption
     */
    public function map(User $user, string $key, array $config): AdminUserOption
    {
        $value = $this->userOptionService->getValue($this->userOptionService->getValues($user), $key, $config);
        return (new AdminUserOptionMapper())->map(
            $key,
            $config,
            $value,
            $this->userOptionService->getLabels($key, $config),
        );
    }

    /**
     * Utilisateur de la session
     * @return User
     * @throws ForbiddenException pour un appel fait avec un token d'API seul
     */
    public function getUser(): User
    {
        return $this->currentUserResolver->requireSession()->getUser();
    }
}
