<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Modifie une option de l'utilisateur de la session, la valeur étant déjà contrôlée par le validateur
 */

namespace App\Api\V2\State\Processor\Admin\System;

use ApiPlatform\Metadata\Operation;
use App\Api\V2\Input\Admin\System\UpdateUserOptionInput;
use App\Api\V2\Resource\Admin\System\AdminUserOption;
use App\Api\V2\State\Processor\Admin\AbstractAdminProcessor;
use App\Api\V2\State\Provider\Admin\System\AdminUserOptionProvider;
use App\Service\Api\Admin\System\ApiAdminUserOptionService;

/**
 * @extends AbstractAdminProcessor<UpdateUserOptionInput, AdminUserOption>
 */
final class UpdateAdminUserOptionProcessor extends AbstractAdminProcessor
{
    public function __construct(
        private readonly ApiAdminUserOptionService $userOptionService,
        private readonly AdminUserOptionProvider $userOptionProvider,
    ) {}

    /**
     * @param mixed $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return AdminUserOption
     */
    protected function write(mixed $data, Operation $operation, array $uriVariables, array $context): AdminUserOption
    {
        $user = $this->userOptionProvider->getUser();
        [$key, $config] = $this->userOptionService->findConfig((string) ($uriVariables['id'] ?? ''));
        $this->userOptionService->update($user, $key, (string) $data->value);
        return $this->userOptionProvider->map($user, $key, $config);
    }

    /**
     * Valeur enregistrée, l'option étant désignée par l'identifiant
     * @param mixed $resource
     * @return string|null
     */
    protected function getLogLabel(mixed $resource): ?string
    {
        return $resource instanceof AdminUserOption ? $resource->value : null;
    }
}
