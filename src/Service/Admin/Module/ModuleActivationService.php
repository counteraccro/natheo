<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Activation des modules du back-office et de leurs opérations d'API, état lu depuis le cache
 */

namespace App\Service\Admin\Module;

use App\Api\V2\Metadata\AdminModuleOperationFinder;
use App\Entity\Admin\System\ModuleActivation;
use App\Enum\Admin\Module\ActivationType;
use App\Enum\Admin\Module\CoreModuleId;
use App\Repository\Admin\System\ModuleActivationRepository;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Service\ResetInterface;

final class ModuleActivationService implements ResetInterface
{
    /**
     * Clé du cache qui garde les éléments désactivés
     * @var string
     */
    public const string CACHE_KEY = 'admin_module_activation';

    /**
     * Éléments désactivés, groupés par type, lus une fois par requête
     * @var array<string, string[]>|null
     */
    private ?array $disabled = null;

    public function __construct(
        private readonly ModuleActivationRepository $repository,
        private readonly CacheInterface $cache,
        private readonly AdminModuleRegistry $moduleRegistry,
        private readonly AdminModuleOperationFinder $operationFinder,
    ) {}

    public function reset(): void
    {
        $this->disabled = null;
    }

    /**
     * Indique si le module est verrouillé. La gestion des modules l'est toujours, sinon plus personne ne pourrait
     * réactiver un module
     * @param string $id
     * @return bool
     */
    public function isModuleLocked(string $id): bool
    {
        return $id === CoreModuleId::MODULE_ACTIVATION->value || ($this->moduleRegistry->get($id)?->locked ?? false);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function isOperationLocked(string $name): bool
    {
        return $this->operationFinder->get($name)['locked'] ?? false;
    }

    /**
     * Désactivation propre au module, sans tenir compte du verrou
     * @param string $id
     * @return bool
     * @throws InvalidArgumentException
     */
    public function isModuleDisabled(string $id): bool
    {
        return in_array($id, $this->getDisabled()[ActivationType::MODULE->value], true);
    }

    /**
     * Désactivation propre à l'opération, sans tenir compte de son module ni du verrou
     * @param string $name
     * @return bool
     * @throws InvalidArgumentException
     */
    public function isOperationDisabled(string $name): bool
    {
        return in_array($name, $this->getDisabled()[ActivationType::OPERATION->value], true);
    }

    /**
     * Un module verrouillé est toujours activé, même si une ligne le désactive
     * @param string $id
     * @return bool
     * @throws InvalidArgumentException
     */
    public function isModuleEnabled(string $id): bool
    {
        return $this->isModuleLocked($id) || !$this->isModuleDisabled($id);
    }

    /**
     * Une opération est activée si son module l'est et si elle n'est pas désactivée (ou verrouillée)
     * @param string $name
     * @return bool
     * @throws InvalidArgumentException
     */
    public function isOperationEnabled(string $name): bool
    {
        $operation = $this->operationFinder->get($name);
        if ($operation === null) {
            return true;
        }
        if (!$this->isModuleEnabled($operation['module'])) {
            return false;
        }
        return $operation['locked'] || !$this->isOperationDisabled($name);
    }

    /**
     * Active ou désactive un module
     * @param string $id
     * @param bool $disabled
     * @return void
     * @throws \LogicException si le module est inconnu, ou verrouillé et à désactiver
     * @throws InvalidArgumentException
     */
    public function setModuleDisabled(string $id, bool $disabled): void
    {
        if ($this->moduleRegistry->get($id) === null) {
            throw new \LogicException(sprintf('Module du back-office « %s » inconnu', $id));
        }
        if ($disabled && $this->isModuleLocked($id)) {
            throw new \LogicException(sprintf('Le module « %s » est verrouillé', $id));
        }
        $this->write(ActivationType::MODULE, $id, $disabled);
    }

    /**
     * Active ou désactive une opération d'API d'un module
     * @param string $name
     * @param bool $disabled
     * @return void
     * @throws \LogicException si l'opération est inconnue, ou verrouillée et à désactiver
     * @throws InvalidArgumentException
     */
    public function setOperationDisabled(string $name, bool $disabled): void
    {
        if ($this->operationFinder->get($name) === null) {
            throw new \LogicException(sprintf('Opération d\'administration « %s » inconnue', $name));
        }
        if ($disabled && $this->isOperationLocked($name)) {
            throw new \LogicException(sprintf('L\'opération « %s » est verrouillée', $name));
        }
        $this->write(ActivationType::OPERATION, $name, $disabled);
    }

    /**
     * Opérations des modules, avec leur verrou
     * @return AdminModuleOperationFinder
     */
    public function getOperationFinder(): AdminModuleOperationFinder
    {
        return $this->operationFinder;
    }

    /**
     * Vide le cache, relu au prochain appel
     * @return void
     * @throws InvalidArgumentException
     */
    public function clearCache(): void
    {
        $this->cache->delete(self::CACHE_KEY);
        $this->disabled = null;
    }

    /**
     * @param ActivationType $type
     * @param string $name
     * @param bool $disabled
     * @return void
     * @throws InvalidArgumentException
     */
    private function write(ActivationType $type, string $name, bool $disabled): void
    {
        $entity = $this->repository->findOneBy(['type' => $type, 'name' => $name]);
        if ($disabled && $entity === null) {
            $this->repository->save((new ModuleActivation())->setType($type)->setName($name), true);
        } elseif (!$disabled && $entity !== null) {
            $this->repository->remove($entity, true);
        }
        $this->clearCache();
    }

    /**
     * @return array<string, string[]>
     * @throws InvalidArgumentException
     */
    private function getDisabled(): array
    {
        return $this->disabled ??= $this->cache->get(self::CACHE_KEY, fn() => $this->repository->findDisabledNames());
    }
}
