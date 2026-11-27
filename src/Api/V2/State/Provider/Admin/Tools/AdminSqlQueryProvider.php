<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider d'une requête du gestionnaire SQL
 */

namespace App\Api\V2\State\Provider\Admin\Tools;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Mapper\Admin\Tools\AdminSqlQueryMapper;
use App\Api\V2\Resource\Admin\Tools\AdminSqlQuery;
use App\Service\Api\Admin\Tools\ApiAdminSqlQueryService;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProviderInterface<AdminSqlQuery>
 */
final readonly class AdminSqlQueryProvider implements ProviderInterface
{
    public function __construct(
        private ApiAdminSqlQueryService $sqlQueryService,
        private TranslatorInterface $translator,
    ) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return AdminSqlQuery
     * @throws NotFoundException
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AdminSqlQuery
    {
        $sqlManager =
            $this->sqlQueryService->find((int) ($uriVariables['id'] ?? 0)) ??
            throw new NotFoundException(
                $this->translator->trans('api_errors.find.sql_query.not_found', domain: 'api_errors'),
            );
        return (new AdminSqlQueryMapper())->map($sqlManager);
    }
}
