<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Enregistre une requête dans le gestionnaire SQL
 */

namespace App\Api\V2\State\Processor\Admin\Tools;

use ApiPlatform\Metadata\Operation;
use App\Api\V2\Input\Admin\Tools\CreateSqlQueryInput;
use App\Api\V2\Mapper\Admin\Tools\AdminSqlQueryMapper;
use App\Api\V2\Resource\Admin\Tools\AdminSqlQuery;
use App\Api\V2\Security\CurrentUserResolver;
use App\Api\V2\State\Processor\Admin\AbstractAdminProcessor;
use App\Service\Api\Admin\Tools\ApiAdminSqlQueryService;

/**
 * @extends AbstractAdminProcessor<CreateSqlQueryInput, AdminSqlQuery>
 */
final class CreateAdminSqlQueryProcessor extends AbstractAdminProcessor
{
    public function __construct(
        private readonly ApiAdminSqlQueryService $sqlQueryService,
        private readonly CurrentUserResolver $currentUser,
    ) {}

    /**
     * @param mixed $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return AdminSqlQuery
     */
    protected function write(mixed $data, Operation $operation, array $uriVariables, array $context): AdminSqlQuery
    {
        $user = $this->currentUser->requireSession()->getUser();
        $sqlManager = $this->sqlQueryService->create($user, $data->name, $data->query);
        return (new AdminSqlQueryMapper())->map($sqlManager);
    }

    /**
     * @param mixed $resource
     * @return string|null
     */
    protected function getLogLabel(mixed $resource): ?string
    {
        return $resource instanceof AdminSqlQuery ? $resource->name : null;
    }
}
