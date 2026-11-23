<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Lecture et écriture de l'activation des modules du back-office et de leurs opérations par l'API d'administration
 */

namespace App\Service\Api\Admin\System;

use App\Api\V2\Exception\ConflictException;
use App\Api\V2\Metadata\AdminModuleOperationFinder;
use App\Service\Admin\Module\AdminModule;
use App\Service\Api\AppApiService;
use Psr\Cache\InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ApiAdminModuleActivationService extends AppApiService
{
    /**
     * Tous les modules, dans l'ordre du menu
     * @return AdminModule[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getModules(): array
    {
        return $this->getAdminMenuBuilder()->getModulesInMenuOrder();
    }

    /**
     * Module de l'identifiant, null s'il n'existe pas
     * @param string $id
     * @return AdminModule|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function findModule(string $id): ?AdminModule
    {
        foreach ($this->getModules() as $module) {
            if ($module->id === $id) {
                return $module;
            }
        }
        return null;
    }

    /**
     * Libellé et description traduits du module
     * @param AdminModule $module
     * @return array{label: string, description: string}
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getModuleTexts(AdminModule $module): array
    {
        return [
            'label' => $module->label->trans($this->getTranslator()),
            'description' => $module->description->trans($this->getTranslator()),
        ];
    }

    /**
     * Opérations du module, triées par nom
     * @param string $module
     * @return array<int, array{name: string, module: string, method: string, path: string, locked: bool}>
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getOperations(string $module): array
    {
        $finder = $this->getOperationFinder();
        return array_map(fn(string $name) => $finder->get($name), $finder->find($module));
    }

    /**
     * Opération du nom, null si elle n'appartient à aucun module
     * @param string $name
     * @return array{name: string, module: string, method: string, path: string, locked: bool}|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function findOperation(string $name): ?array
    {
        return $this->getOperationFinder()->get($name);
    }

    /**
     * Désactivation propre du module, toujours fausse pour un module verrouillé
     * @param string $id
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidArgumentException
     */
    public function isModuleDisabled(string $id): bool
    {
        return !$this->getModuleActivationService()->isModuleEnabled($id);
    }

    /**
     * Désactivation propre de l'opération, sans tenir compte de son module, toujours fausse si elle est verrouillée
     * @param array{name: string, locked: bool} $operation
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidArgumentException
     */
    public function isOperationDisabled(array $operation): bool
    {
        return !$operation['locked'] && $this->getModuleActivationService()->isOperationDisabled($operation['name']);
    }

    /**
     * @param string $id
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function isModuleLocked(string $id): bool
    {
        return $this->getModuleActivationService()->isModuleLocked($id);
    }

    /**
     * Active ou désactive un module
     * @param string $id
     * @param bool $disabled
     * @return void
     * @throws ConflictException si le module est verrouillé
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidArgumentException
     */
    public function setModuleDisabled(string $id, bool $disabled): void
    {
        $service = $this->getModuleActivationService();
        if ($disabled && $service->isModuleLocked($id)) {
            throw new ConflictException(
                $this->getTranslator()->trans('module_activation.error.module_locked', domain: 'module_activation'),
            );
        }
        $service->setModuleDisabled($id, $disabled);
    }

    /**
     * Active ou désactive une opération
     * @param string $name
     * @param bool $disabled
     * @return void
     * @throws ConflictException si l'opération est verrouillée
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidArgumentException
     */
    public function setOperationDisabled(string $name, bool $disabled): void
    {
        $service = $this->getModuleActivationService();
        if ($disabled && $service->isOperationLocked($name)) {
            throw new ConflictException(
                $this->getTranslator()->trans('module_activation.error.operation_locked', domain: 'module_activation'),
            );
        }
        $service->setOperationDisabled($name, $disabled);
    }

    /**
     * @return AdminModuleOperationFinder
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getOperationFinder(): AdminModuleOperationFinder
    {
        return $this->getModuleActivationService()->getOperationFinder();
    }
}
