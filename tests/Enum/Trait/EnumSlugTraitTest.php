<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de l'EnumSlugTrait
 */

namespace App\Tests\Enum\Trait;

use App\Enum\Admin\Comment\CommentStatus;
use App\Enum\Admin\Content\Menu\MenuPosition;
use PHPUnit\Framework\TestCase;

class EnumSlugTraitTest extends TestCase
{
    /**
     * Le slug est le nom du cas en minuscules
     * @return void
     */
    public function testGetSlug(): void
    {
        $this->assertSame('wait_validation', CommentStatus::WAIT_VALIDATION->getSlug());
        $this->assertSame(['wait_validation', 'validate', 'moderate'], CommentStatus::getSlugs());
    }

    /**
     * Retrouve le cas par son slug, null si le slug est inconnu ou absent
     * @return void
     */
    public function testTryFromSlug(): void
    {
        $this->assertSame(CommentStatus::MODERATE, CommentStatus::tryFromSlug('moderate'));
        $this->assertNull(CommentStatus::tryFromSlug('MODERATE'));
        $this->assertNull(CommentStatus::tryFromSlug('inconnu'));
        $this->assertNull(CommentStatus::tryFromSlug(null));
    }

    /**
     * Un enum qui redéfinit getSlug() s'en sert aussi pour tryFromSlug() et getSlugs()
     * @return void
     */
    public function testOverriddenSlug(): void
    {
        $this->assertSame('header', MenuPosition::POSITION_HEADER->getSlug());
        $this->assertSame(MenuPosition::POSITION_LEFT, MenuPosition::tryFromSlug('left'));
        $this->assertNull(MenuPosition::tryFromSlug('position_left'));
        $this->assertSame(['header', 'right', 'footer', 'left'], MenuPosition::getSlugs());
    }
}
