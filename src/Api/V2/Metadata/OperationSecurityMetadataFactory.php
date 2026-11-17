<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Sécurité des opérations selon le type du token : publiques, partagées par les deux types, ou d'administration
 * (construite depuis leur module)
 */

namespace App\Api\V2\Metadata;

use ApiPlatform\Metadata\ErrorResource;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceMetadataCollection;
use App\Enum\Admin\System\ApiToken\ApiTokenScope;
use App\Security\ApiUser;
use App\Service\Admin\Module\AdminModuleRegistry;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;

#[AsDecorator(decorates: 'api_platform.metadata.resource.metadata_collection_factory', priority: 0)]
final readonly class OperationSecurityMetadataFactory implements ResourceMetadataCollectionFactoryInterface
{
    /**
     * Clé d'extraProperties d'une opération ouverte aux tokens publics et back-office (sessions, infos)
     * @var string
     */
    public const string SHARED = 'shared';

    /**
     * Clé d'extraProperties qui rattache une opération d'administration (/admin/…) à son module
     * @var string
     */
    public const string MODULE = 'module';

    /**
     * Clé d'extraProperties d'une opération d'administration qui accepte aussi une session ouverte par un token public
     * @var string
     */
    public const string FRONT_ALLOWED = 'frontAllowed';

    /**
     * Préfixe des adresses des opérations d'administration
     * @var string
     */
    public const string ADMIN_PREFIX = '/admin/';

    public function __construct(
        #[AutowireDecorated] private ResourceMetadataCollectionFactoryInterface $decorated,
        private AdminModuleRegistry $adminModuleRegistry,
    ) {}

    /**
     * @param string $resourceClass
     * @return ResourceMetadataCollection
     */
    public function create(string $resourceClass): ResourceMetadataCollection
    {
        $collection = $this->decorated->create($resourceClass);

        foreach ($collection as $index => $resource) {
            $operations = $resource->getOperations();
            if ($resource instanceof ErrorResource || $operations === null) {
                continue;
            }
            foreach ($operations as $name => $operation) {
                $operations->add($name, $operation->withSecurity($this->buildSecurity($operation)));
            }
            $collection[$index] = $resource->withOperations($operations);
        }
        return $collection;
    }

    /**
     * Expression de sécurité de l'opération, croisée avec le type du token
     * @param Operation $operation
     * @return string|null
     */
    private function buildSecurity(Operation $operation): ?string
    {
        $security = $operation->getSecurity();
        $extra = $operation->getExtraProperties();
        $module = $extra[self::MODULE] ?? null;
        $isAdminPath = str_starts_with((string) $operation->getUriTemplate(), self::ADMIN_PREFIX);

        if ($module !== null || $isAdminPath) {
            return $this->combine($this->buildAdminCondition($operation, $module, $isAdminPath), $security);
        }
        if (($extra[self::SHARED] ?? false) === true) {
            return $security;
        }
        return $this->combine($this->isGranted(ApiTokenScope::PUBLIC->getRole()), $security);
    }

    /**
     * Condition d'une opération d'administration : session ouverte par un token back-office (ou public avec
     * frontAllowed) et rôle du module ; les droits sont ceux de l'utilisateur
     * @param Operation $operation
     * @param mixed $module
     * @param bool $isAdminPath
     * @return string
     * @throws \LogicException si l'opération et son adresse ne correspondent pas, ou si le module est inconnu
     */
    private function buildAdminCondition(Operation $operation, mixed $module, bool $isAdminPath): string
    {
        if (!is_string($module) || !$isAdminPath) {
            throw new \LogicException(
                sprintf(
                    'L\'opération « %s » doit avoir une adresse en %s et un module (extraProperties « %s »)',
                    $operation->getName(),
                    self::ADMIN_PREFIX,
                    self::MODULE,
                ),
            );
        }
        $definition =
            $this->adminModuleRegistry->get($module) ??
            throw new \LogicException(
                sprintf('Module « %s » inconnu pour l\'opération « %s »', $module, $operation->getName()),
            );

        $conditions = [];
        if (($operation->getExtraProperties()[self::FRONT_ALLOWED] ?? false) !== true) {
            $conditions[] = $this->isGranted(ApiTokenScope::BACK_OFFICE->getRole());
        }
        $conditions[] = $this->isGranted(ApiUser::ROLE_USER_SESSION);
        $conditions[] = $this->isGranted($definition->role);
        return implode(' and ', $conditions);
    }

    /**
     * @param string $role
     * @return string
     */
    private function isGranted(string $role): string
    {
        return sprintf("is_granted('%s')", $role);
    }

    /**
     * @param string $condition
     * @param string|null $security
     * @return string
     */
    private function combine(string $condition, ?string $security): string
    {
        return $security === null || $security === '' ? $condition : sprintf('%s and (%s)', $condition, $security);
    }
}
