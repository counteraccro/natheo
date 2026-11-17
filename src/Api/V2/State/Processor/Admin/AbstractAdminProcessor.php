<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Base des processors d'administration : contrôle If-Match avant l'écriture
 */

namespace App\Api\V2\State\Processor\Admin;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Api\V2\Concurrency\Etag;
use App\Api\V2\Concurrency\VersionedResourceInterface;
use App\Api\V2\Exception\PreconditionFailedException;
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

    /**
     * @param TranslatorInterface $translator
     * @return void
     */
    #[Required]
    public function setAdminProcessorTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
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
        $this->checkPrecondition($context['previous_data'] ?? $data, $context['request'] ?? null);
        return $this->write($data, $operation, $uriVariables, $context);
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
