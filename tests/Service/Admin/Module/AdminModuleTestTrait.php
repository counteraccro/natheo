<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Modules et menus construits à la volée pour les tests des registres et de la sidebar
 */

namespace App\Tests\Service\Admin\Module;

use App\Service\Admin\Module\AdminMenuBadgeInterface;
use App\Service\Admin\Module\AdminMenuItem;
use App\Service\Admin\Module\AdminMenuProviderInterface;
use App\Service\Admin\Module\AdminModule;
use App\Service\Admin\Module\AdminModuleInterface;
use Symfony\Component\Translation\TranslatableMessage;

trait AdminModuleTestTrait
{
    /**
     * @param string $id
     * @param string $role
     * @param AdminMenuItem[] $items
     * @param AdminModule|null $definition
     * @param array<string, int>|null $badges badges du module, null pour un module sans badge
     * @return AdminModuleInterface
     */
    private function createTestModule(
        string $id,
        string $role = 'ROLE_CONTRIBUTEUR',
        array $items = [],
        ?AdminModule $definition = null,
        ?array $badges = null,
    ): AdminModuleInterface {
        $definition ??= new AdminModule(
            $id,
            $role,
            new TranslatableMessage($id . '.label'),
            new TranslatableMessage($id . '.description'),
        );
        if ($badges === null) {
            return new class ($definition, $items) implements AdminModuleInterface {
                public function __construct(private readonly AdminModule $definition, private readonly array $items) {}

                public function getDefinition(): AdminModule
                {
                    return $this->definition;
                }

                public function getMenuItems(): array
                {
                    return $this->items;
                }
            };
        }

        return new class ($definition, $items, $badges) implements AdminModuleInterface, AdminMenuBadgeInterface {
            public function __construct(
                private readonly AdminModule $definition,
                private readonly array $items,
                private readonly array $badges,
            ) {}

            public function getDefinition(): AdminModule
            {
                return $this->definition;
            }

            public function getMenuItems(): array
            {
                return $this->items;
            }

            public function getBadges(): array
            {
                return $this->badges;
            }
        };
    }

    /**
     * @param AdminMenuItem[] $items
     * @return AdminMenuProviderInterface
     */
    private function createTestMenu(array $items): AdminMenuProviderInterface
    {
        return new class ($items) implements AdminMenuProviderInterface {
            public function __construct(private readonly array $items) {}

            public function getMenuItems(): array
            {
                return $this->items;
            }
        };
    }

    /**
     * @param string $id
     * @param string|null $parent
     * @param int $position
     * @param string|null $route
     * @param array $options autres arguments nommés de AdminMenuItem
     * @return AdminMenuItem
     */
    private function createTestMenuItem(
        string $id,
        ?string $parent = null,
        int $position = 0,
        ?string $route = null,
        array $options = [],
    ): AdminMenuItem {
        return new AdminMenuItem(
            ...[
                'id' => $id,
                'label' => new TranslatableMessage($id),
                'icon' => 'M0 0',
                'parent' => $parent,
                'position' => $position,
                'legacyRoute' => $route,
                ...$options,
            ],
        );
    }
}
