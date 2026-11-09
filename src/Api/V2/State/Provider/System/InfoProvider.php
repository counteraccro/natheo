<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider de la ressource Info
 */

namespace App\Api\V2\State\Provider\System;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Resource\System\Info;
use App\Service\Api\System\ApiInfoService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @implements ProviderInterface<Info>
 */
final readonly class InfoProvider implements ProviderInterface
{
    public function __construct(private ApiInfoService $apiInfoService) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return Info
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Info
    {
        return $this->apiInfoService->getInfo();
    }
}
