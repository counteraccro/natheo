<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider de la liste des options système publiques
 */

namespace App\Api\V2\State\Provider\System;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Resource\System\OptionSystem;
use App\Service\Api\System\ApiOptionSystemService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @implements ProviderInterface<OptionSystem>
 */
final readonly class OptionSystemCollectionProvider implements ProviderInterface
{
    public function __construct(private ApiOptionSystemService $apiOptionSystemService) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return OptionSystem[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        return array_map(
            fn(array $option) => new OptionSystem(...$option),
            $this->apiOptionSystemService->getPublicOptions(),
        );
    }
}
