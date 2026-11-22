<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider des options de l'utilisateur de la session, dans l'ordre de options_user.yaml
 */

namespace App\Api\V2\State\Provider\Admin\System;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Exception\ForbiddenException;
use App\Api\V2\Mapper\Admin\System\AdminUserOptionMapper;
use App\Api\V2\Resource\Admin\System\AdminUserOption;
use App\Api\V2\Security\CurrentUserResolver;
use App\Service\Api\Admin\System\ApiAdminUserOptionService;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProviderInterface<AdminUserOption>
 */
final readonly class AdminUserOptionCollectionProvider implements ProviderInterface
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
     * @return AdminUserOption[]
     * @throws ForbiddenException
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $values = $this->userOptionService->getValues($this->currentUserResolver->requireSession()->getUser());
        $mapper = new AdminUserOptionMapper($this->translator);
        $options = [];
        foreach ($this->userOptionService->getConfigs() as $key => $config) {
            $options[] = $mapper->map($key, $config, $this->userOptionService->getValue($values, $key, $config));
        }
        return $options;
    }
}
