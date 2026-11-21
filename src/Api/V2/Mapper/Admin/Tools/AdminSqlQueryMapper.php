<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Conversion d'une requête du gestionnaire SQL en ressource AdminSqlQuery
 */

namespace App\Api\V2\Mapper\Admin\Tools;

use App\Api\V2\Resource\Admin\Tools\AdminSqlQuery;
use App\Entity\Admin\Tools\SqlManager;

final readonly class AdminSqlQueryMapper
{
    /**
     * @param SqlManager $sqlManager
     * @return AdminSqlQuery
     */
    public function map(SqlManager $sqlManager): AdminSqlQuery
    {
        $resource = new AdminSqlQuery();
        $resource->id = (int) $sqlManager->getId();
        $resource->name = (string) $sqlManager->getName();
        $resource->query = (string) $sqlManager->getQuery();
        $resource->disabled = (bool) $sqlManager->isDisabled();
        $resource->createdAt = $sqlManager->getCreatedAt()?->format(\DateTimeInterface::ATOM);
        $resource->updatedAt = $sqlManager->getUpdateAt()?->format(\DateTimeInterface::ATOM);
        return $resource;
    }
}
