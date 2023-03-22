<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * EventListener qui va intercepter tout event venant de doctrine
 */
namespace App\EventListener;

use App\Entity\Admin\SidebarElement;
use App\Service\Admin\OptionSystemService;
use App\Service\LoggerService;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class DatabaseActivitySubscriber implements EventSubscriberInterface
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
        return [
            Events::postPersist,
            Events::postRemove,
            Events::postUpdate,
        ];
    }

    /**
     * Callback persist
     * @param LifecycleEventArgs $args
     * @return void
     */
    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->logActivity(LoggerService::ACTION_DOCTRINE_PERSIST, $args);
    }

    /**
     * Callback remove
     * @param LifecycleEventArgs $args
     * @return void
     */
    public function postRemove(LifecycleEventArgs $args): void
    {
        $this->logActivity(LoggerService::ACTION_DOCTRINE_REMOVE, $args);
    }

    /**
     * Callback update
     * @param LifecycleEventArgs $args
     * @return void
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
     */
    private function logActivity(string $action, LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        $value = $this->optionSystemService->getValueByKey(OptionSystemService::OS_LOG_DOCTRINE);

        if($value !== '1')
        {
            return;
        }

        // if this subscriber only applies to certain entity types,
        // add some code to check the entity type as early as possible
        if ($entity instanceof SidebarElement) {
            $this->loggerService->logDoctrine($action, 'SidebarElement', $entity->getId());
        }

        // ... get the entity information and log it somehow
    }
}