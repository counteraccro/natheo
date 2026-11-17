<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Base des processors d'administration : contrôle If-Match avant l'écriture, journal des actions après
 */

namespace App\Api\V2\State\Processor\Admin;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Api\V2\Concurrency\Etag;
use App\Api\V2\Concurrency\VersionedResourceInterface;
use App\Api\V2\Exception\PreconditionFailedException;
use App\Api\V2\Metadata\OperationSecurityMetadataFactory;
use App\Api\V2\Security\CurrentUserResolver;
use App\Enum\Admin\System\ActionLog\ActionLogAction;
use App\Service\Api\Admin\ActionLogService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @template T1
 * @template T2
 * @implements ProcessorInterface<T1, T2>
 */
abstract class AbstractAdminProcessor implements ProcessorInterface
{
    private TranslatorInterface $translator;

    private ActionLogService $actionLogService;

    private CurrentUserResolver $currentUserResolver;

    /**
     * Dépendances de la base, injectées sans passer par le constructeur des processors
     * @param TranslatorInterface $translator
     * @param ActionLogService $actionLogService
     * @param CurrentUserResolver $currentUserResolver
     * @return void
     */
    #[Required]
    public function setAdminProcessorServices(
        TranslatorInterface $translator,
        ActionLogService $actionLogService,
        CurrentUserResolver $currentUserResolver,
    ): void {
        $this->translator = $translator;
        $this->actionLogService = $actionLogService;
        $this->currentUserResolver = $currentUserResolver;
    }

    /**
     * @param mixed $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return mixed
     * @throws PreconditionFailedException si l'élément a changé depuis la version envoyée dans If-Match
     */
    final public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): mixed {
        $current = $context['previous_data'] ?? $data;
        $this->checkPrecondition($current, $context['request'] ?? null);
        $result = $this->write($data, $operation, $uriVariables, $context);

        $module = $operation->getExtraProperties()[OperationSecurityMetadataFactory::MODULE] ?? null;
        if (is_string($module)) {
            $resource = $result ?? $current;
            $this->actionLogService->log(
                $this->currentUserResolver->getSession(),
                $module,
                $this->getLogAction($operation, $data),
                (string) $operation->getShortName(),
                $this->getLogResourceId($resource, $uriVariables),
                $this->getLogLabel($resource),
            );
        }
        return $result;
    }

    /**
     * Écriture de l'opération
     * @param mixed $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return mixed
     */
    abstract protected function write(mixed $data, Operation $operation, array $uriVariables, array $context): mixed;

    /**
     * Action tracée dans le journal, déduite de la méthode HTTP par défaut
     * @param Operation $operation
     * @param mixed $data
     * @return string valeur de ActionLogAction ou action propre au module
     */
    protected function getLogAction(Operation $operation, mixed $data): string
    {
        return ActionLogAction::fromMethod($operation->getMethod())->value;
    }

    /**
     * Libellé de l'élément tracé dans le journal
     * @param mixed $resource ressource écrite, ou telle qu'avant sa suppression
     * @return string|null
     */
    protected function getLogLabel(mixed $resource): ?string
    {
        return null;
    }

    /**
     * Identifiant de l'élément tracé dans le journal : variable d'URI id, sinon propriété id de la ressource
     * @param mixed $resource
     * @param array $uriVariables
     * @return string|null
     */
    protected function getLogResourceId(mixed $resource, array $uriVariables): ?string
    {
        $id = $uriVariables['id'] ?? (is_object($resource) && isset($resource->id) ? $resource->id : null);
        return is_scalar($id) ? (string) $id : null;
    }

    /**
     * Sans If-Match l'écriture est acceptée (scripts d'intégration) ; avec, il doit désigner la version lue
     * @param mixed $current élément tel qu'il était avant l'écriture
     * @param Request|null $request
     * @return void
     * @throws PreconditionFailedException
     */
    private function checkPrecondition(mixed $current, ?Request $request): void
    {
        $ifMatch = $request?->headers->get('If-Match');
        if (
            $ifMatch === null ||
            !($current instanceof VersionedResourceInterface) ||
            Etag::matches($ifMatch, $current)
        ) {
            return;
        }
        throw new PreconditionFailedException(
            $this->translator->trans('api_errors.precondition.failed', domain: 'api_errors'),
        );
    }
}
