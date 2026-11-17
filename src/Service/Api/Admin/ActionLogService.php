<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Écriture du journal des actions du back-office pour l'utilisateur de la session API
 */

namespace App\Service\Api\Admin;

use App\Entity\Admin\System\ActionLog;
use App\Security\ApiUser;
use App\Service\Api\AppApiService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ActionLogService extends AppApiService
{
    /**
     * Trace une action ; l'auteur et l'utilisateur d'origine viennent de la session API de la requête
     * @param string $module
     * @param string $action valeur de ActionLogAction ou action propre au module
     * @param string $resource
     * @param string|null $resourceId
     * @param string|null $label libellé de l'élément au moment de l'action
     * @return ActionLog
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function log(
        string $module,
        string $action,
        string $resource,
        ?string $resourceId = null,
        ?string $label = null,
    ): ActionLog {
        $apiUser = $this->getSecurity()->getUser();
        $session = $apiUser instanceof ApiUser ? $apiUser->getSession() : null;

        $actionLog = (new ActionLog())
            ->setUser($session?->getUser())
            ->setImpersonator($session?->getImpersonator())
            ->setModule($module)
            ->setAction($action)
            ->setResource($resource)
            ->setResourceId($resourceId)
            ->setLabel($label)
            ->setIp($this->getRequestStack()->getCurrentRequest()?->getClientIp());
        $this->save($actionLog);
        return $actionLog;
    }
}
