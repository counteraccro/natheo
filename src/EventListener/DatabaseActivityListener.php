<?php

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * EventListener qui va intercepter tout event venant de doctrine
 */

namespace App\EventListener;

use App\Service\Admin\System\OptionSystemService;
use App\Service\LoggerService;
use App\Utils\System\Options\OptionSystemKey;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

#[AsDoctrineListener('postPersist')]
#[AsDoctrineListener('postRemove')]
#[AsDoctrineListener('postUpdate')]
class DatabaseActivityListener
{
    private OptionSystemService $optionSystemService;

    private LoggerService $loggerService;

    public function __construct(OptionSystemService $optionSystemService, LoggerService $loggerService)
    {
        $this->optionSystemService = $optionSystemService;
        $this->loggerService = $loggerService;
    }

    // this method can only return the event names; you cannot define a
    // custom method name to execute when each event triggers
    public function getSubscribedEvents(): array
    {
        return [Events::postPersist, Events::postRemove, Events::postUpdate];
    }

    /**
     * Callback persist
     * @param LifecycleEventArgs $args
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->logActivity(LoggerService::ACTION_DOCTRINE_PERSIST, $args);
    }

    /**
     * Callback remove
     * @param LifecycleEventArgs $args
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function postRemove(LifecycleEventArgs $args): void
    {
        $this->logActivity(LoggerService::ACTION_DOCTRINE_REMOVE, $args);
    }

    /**
     * Callback update
     * @param LifecycleEventArgs $args
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->logActivity(LoggerService::ACTION_DOCTRINE_UPDATE, $args);
    }

    /**
     * Log des activités
     * @param string $action
     * @param LifecycleEventArgs $args
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function logActivity(string $action, LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        $value = $this->optionSystemService->getValueByKey(OptionSystemKey::OS_LOG_DOCTRINE);

        if ($value !== '1') {
            return;
        }

        $class = substr(strrchr($entity::class, '\\'), 1);
        $this->loggerService->logDoctrine($action, $class, $entity->getId());
    }
}
