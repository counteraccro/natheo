<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider API v2 : domaines de traduction servis par l'API
 */

namespace App\Api\V2\State\Provider\Admin\System;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Mapper\Admin\System\AdminTranslationCatalogueMapper;
use App\Api\V2\Resource\Admin\System\AdminTranslationCatalogue;
use App\Service\Api\System\TranslationCatalogueService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @implements ProviderInterface<AdminTranslationCatalogue>
 */
final readonly class AdminTranslationCatalogueCollectionProvider implements ProviderInterface
{
    public function __construct(private TranslationCatalogueService $catalogueService) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return AdminTranslationCatalogue[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $mapper = new AdminTranslationCatalogueMapper();
        return array_map(
            fn(string $domain): AdminTranslationCatalogue => $mapper->mapSummary($domain),
            $this->catalogueService->getDomains(),
        );
    }
}
