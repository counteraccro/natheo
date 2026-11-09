<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Traduit le titre des erreurs de l'API v2 (erreurs et violations de validation) selon leur statut HTTP
 */

namespace App\Api\V2\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ApiResource\Error;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProviderInterface<object>
 */
#[AsDecorator(decorates: 'api_platform.state.error_provider')]
#[AsDecorator(decorates: 'api_platform.validator.state.error_provider')]
final readonly class TranslatedErrorProvider implements ProviderInterface
{
    /**
     * Statuts ayant un titre traduit (api_errors.title.<statut>), les autres prennent api_errors.title.default
     * @var int[]
     */
    private const array TITLED_STATUSES = [400, 401, 403, 404, 405, 406, 409, 415, 422, 429, 500];

    public function __construct(
        #[AutowireDecorated] private ProviderInterface $decorated,
        private TranslatorInterface $translator,
    ) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return object
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object
    {
        $error = $this->decorated->provide($operation, $uriVariables, $context);

        if ($error instanceof Error) {
            $error->setTitle($this->getTitle($error->getStatus() ?? 500));
        } elseif ($error instanceof ValidationException) {
            // Titre fixé à la construction : l'exception est recréée avec les mêmes violations
            $translated = new ValidationException(
                $error->getConstraintViolationList(),
                $error->getCode(),
                $error->getPrevious(),
                $this->getTitle($error->getStatus() ?? 422),
            );
            $translated->setStatus($error->getStatus() ?? 422);
            $error = $translated;
        }
        return $error;
    }

    /**
     * Titre traduit d'un statut HTTP
     * @param int $status
     * @return string
     */
    private function getTitle(int $status): string
    {
        $key = in_array($status, self::TITLED_STATUSES, true) ? (string) $status : 'default';
        return $this->translator->trans('api_errors.title.' . $key, domain: 'api_errors');
    }
}
