<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.2
 * Opérations d'API rattachées à chaque module du back-office, avec leur verrou
 */

namespace App\Api\V2\Metadata;

use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceNameCollection;
use App\Enum\Admin\Module\CoreModuleId;

final class AdminModuleOperationFinder
{
    /**
     * Préfixe du nom de toute opération d'un module
     * @var string
     */
    public const string NAME_PREFIX = 'admin_';

    /**
     * Opérations indexées par nom, construites au premier appel
     * @var array<string, array{name: string, module: string, method: string, path: string, locked: bool}>|null
     */
    private ?array $operations = null;

    public function __construct(
        private readonly ResourceNameCollectionFactoryInterface $resourceNameCollectionFactory,
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
    ) {}

    /**
     * Noms des opérations du module, triés
     * @param string $module
     * @return string[]
     */
    public function find(string $module): array
    {
        return array_keys(array_filter($this->all(), fn(array $operation) => $operation['module'] === $module));
    }

    /**
     * Opération du nom, null si elle n'appartient à aucun module
     * @param string $name
     * @return array{name: string, module: string, method: string, path: string, locked: bool}|null
     */
    public function get(string $name): ?array
    {
        if (!str_starts_with($name, self::NAME_PREFIX)) {
            return null;
        }
        return $this->all()[$name] ?? null;
    }

    /**
     * Toutes les opérations des modules, triées par nom. Les opérations de la gestion des modules sont toujours
     * verrouillées, pour qu'elle reste accessible
     * @return array<string, array{name: string, module: string, method: string, path: string, locked: bool}>
     */
    public function all(): array
    {
        return $this->operations ??= $this->build($this->resourceNameCollectionFactory->create());
    }

    /**
     * @param ResourceNameCollection $resourceClasses
     * @return array<string, array{name: string, module: string, method: string, path: string, locked: bool}>
     */
    private function build(ResourceNameCollection $resourceClasses): array
    {
        $operations = [];
        foreach ($resourceClasses as $resourceClass) {
            foreach ($this->resourceMetadataCollectionFactory->create($resourceClass) as $resource) {
                foreach ($resource->getOperations() ?? [] as $operation) {
                    $extra = $operation->getExtraProperties();
                    $module = $extra[OperationSecurityMetadataFactory::MODULE] ?? null;
                    $name = $operation->getName();
                    if (!is_string($module) || $name === null || isset($operations[$name])) {
                        continue;
                    }
                    $operations[$name] = [
                        'name' => $name,
                        'module' => $module,
                        'method' => $operation instanceof HttpOperation ? $operation->getMethod() : 'GET',
                        'path' => $operation instanceof HttpOperation ? (string) $operation->getUriTemplate() : '',
                        'locked' =>
                            ($extra[OperationSecurityMetadataFactory::LOCKED] ?? false) === true ||
                            $module === CoreModuleId::MODULE_ACTIVATION->value,
                    ];
                }
            }
        }
        ksort($operations);
        return $operations;
    }
}
