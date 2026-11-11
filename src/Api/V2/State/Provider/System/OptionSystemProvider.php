<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider d'une option système publique
 */

namespace App\Api\V2\State\Provider\System;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Resource\System\OptionSystem;
use App\Service\Api\System\ApiOptionSystemService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProviderInterface<OptionSystem>
 */
final readonly class OptionSystemProvider implements ProviderInterface
{
    public function __construct(
        private ApiOptionSystemService $apiOptionSystemService,
        private TranslatorInterface $translator,
    ) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return OptionSystem
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): OptionSystem
    {
        $option = $this->apiOptionSystemService->findPublicOption((string) $uriVariables['key']);
        if ($option === null) {
            throw new NotFoundException(
                $this->translator->trans('api_errors.find.option.system.not.found', domain: 'api_errors'),
            );
        }
        return new OptionSystem(...$option);
    }
}
