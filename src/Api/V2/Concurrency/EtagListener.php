<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ajoute l'ETag aux réponses des opérations d'administration qui renvoient une ressource versionnée
 */

namespace App\Api\V2\Concurrency;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\HttpOperation;
use App\Api\V2\Metadata\OperationSecurityMetadataFactory;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::RESPONSE)]
final class EtagListener
{
    /**
     * @param ResponseEvent $event
     * @return void
     */
    public function __invoke(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $operation = $request->attributes->get('_api_operation');
        $data = $request->attributes->get('data');
        if (
            !($operation instanceof HttpOperation) ||
            $operation instanceof CollectionOperationInterface ||
            $operation->getMethod() === HttpOperation::METHOD_DELETE ||
            !isset($operation->getExtraProperties()[OperationSecurityMetadataFactory::MODULE]) ||
            !($data instanceof VersionedResourceInterface) ||
            !$event->getResponse()->isSuccessful()
        ) {
            return;
        }

        $etag = Etag::of($data);
        if ($etag !== null) {
            $event->getResponse()->headers->set('ETag', $etag);
        }
    }
}
