<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de l'enum Role
 */

namespace App\Tests\Enum\Admin\System\User;

use App\Enum\Admin\System\User\Role;
use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
    /**
     * Le rôle le plus élevé ne dépend pas de l'ordre des rôles, null sans rôle du back-office
     * @return void
     */
    public function testHighest(): void
    {
        $this->assertSame(Role::ADMIN, Role::highest(['ROLE_USER', 'ROLE_ADMIN', 'ROLE_CONTRIBUTEUR']));
        $this->assertSame(Role::SUPER_ADMIN, Role::highest(['ROLE_SUPER_ADMIN', 'ROLE_ALLOWED_TO_SWITCH']));
        $this->assertSame(Role::USER, Role::highest(['ROLE_USER']));
        $this->assertNull(Role::highest(['ROLE_BUNDLE']));
        $this->assertNull(Role::highest([]));
    }
}
