<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Répond 404 aux opérations d'API désactivées et aux pages d'un module désactivé du back-office, avant le pare-feu
 * pour qu'un appelant non connecté n'apprenne rien de plus
 */

namespace App\EventListener;

use App\Api\V2\Exception\NotFoundException;
use App\Service\Admin\Module\AdminModuleRegistry;
use App\Service\Admin\Module\ModuleActivationService;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsEventListener(event: KernelEvents::REQUEST, priority: 9)]
final readonly class ModuleActivationListener
{
    /**
     * Préfixe des routes du back-office
     * @var string
     */
    private const string ADMIN_ROUTE_PREFIX = 'admin_';

    public function __construct(
        private ModuleActivationService $activationService,
        private AdminModuleRegistry $moduleRegistry,
        private TranslatorInterface $translator,
    ) {}

    /**
     * @param RequestEvent $event
     * @return void
     * @throws InvalidArgumentException
     */
    public function __invoke(RequestEvent $event): void
    {
        $attributes = $event->getRequest()->attributes;

        $operation = $attributes->get('_api_operation_name');
        if (is_string($operation)) {
            if (!$this->activationService->isOperationEnabled($operation)) {
                throw new NotFoundException(
                    $this->translator->trans('module_activation.error.disabled', domain: 'module_activation'),
                );
            }
            return;
        }

        $route = $attributes->get('_route');
        if (!is_string($route) || !str_starts_with($route, self::ADMIN_ROUTE_PREFIX)) {
            return;
        }
        foreach ($this->moduleRegistry->all() as $module) {
            if ($module->hasRoute($route) && !$this->activationService->isModuleEnabled($module->id)) {
                throw new NotFoundHttpException();
            }
        }
    }
}
