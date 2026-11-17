<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Écriture du journal des actions du back-office
 */

namespace App\Service\Api\Admin;

use App\Entity\Admin\System\ActionLog;
use App\Entity\Admin\System\UserSession;
use App\Service\Api\AppApiService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ActionLogService extends AppApiService
{
    /**
     * Trace une action, l'auteur et l'utilisateur d'origine étant ceux de la session
     * @param UserSession|null $session session de l'auteur, null pour un appel fait avec un token d'API seul
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
        ?UserSession $session,
        string $module,
        string $action,
        string $resource,
        ?string $resourceId = null,
        ?string $label = null,
    ): ActionLog {
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
