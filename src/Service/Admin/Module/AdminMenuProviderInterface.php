<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Fournit des entrées au menu du back-office
 */

namespace App\Service\Admin\Module;

interface AdminMenuProviderInterface
{
    /**
     * @return AdminMenuItem[]
     */
    public function getMenuItems(): array;
}
