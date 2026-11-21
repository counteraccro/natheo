<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Requêtes du gestionnaire SQL lues et enregistrées par l'API d'administration
 */

namespace App\Service\Api\Admin\Tools;

use App\Entity\Admin\System\User;
use App\Entity\Admin\Tools\SqlManager;
use App\Service\Api\AppApiService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ApiAdminSqlQueryService extends AppApiService
{
    /**
     * Enregistre une requête active, nommée par défaut quand le nom est vide
     * @param User $user
     * @param string|null $name
     * @param string $query
     * @return SqlManager
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function create(User $user, ?string $name, string $query): SqlManager
    {
        $name = trim((string) $name);
        $sqlManager = (new SqlManager())
            ->setUser($user)
            ->setName(
                $name !== ''
                    ? $name
                    : $this->getTranslator()->trans('sql_manager.api.default_name', domain: 'sql_manager'),
            )
            ->setQuery($query)
            ->setDisabled(false);
        $this->save($sqlManager);
        return $sqlManager;
    }

    /**
     * @param int $id
     * @return SqlManager|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function find(int $id): ?SqlManager
    {
        $sqlManager = $this->findOneById(SqlManager::class, $id);
        return $sqlManager instanceof SqlManager ? $sqlManager : null;
    }
}
