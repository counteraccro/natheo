<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de la conversion d'une entrée du menu en ressource AdminMenuItem
 */

namespace App\Tests\Api\V2\Mapper\Admin\System;

use App\Api\V2\Mapper\Admin\System\AdminMenuItemMapper;
use App\Service\Admin\Module\AdminMenuItem;
use App\Service\Admin\Module\AdminMenuNode;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\TranslatableMessage;

class AdminMenuItemMapperTest extends TestCase
{
    /**
     * Page Twig : adresse dans legacyUrl, sans route ni chemin de l'application
     * @return void
     */
    public function testMapLegacyRoute(): void
    {
        $item = new AdminMenuItem('tag_index', new TranslatableMessage('tag'), 'M0 0', legacyRoute: 'admin_menu_index');
        $resource = (new AdminMenuItemMapper())->map(new AdminMenuNode($item, 'tag', 'Tags', '/admin/fr/tag/'));

        $this->assertSame('/admin/fr/tag/', $resource->legacyUrl);
        $this->assertNull($resource->route);
        $this->assertNull($resource->path);
    }

    /**
     * Écran de l'application : route Vue et chemin, sans legacyUrl
     * @return void
     */
    public function testMapAppRoute(): void
    {
        $item = new AdminMenuItem(
            'tag_index',
            new TranslatableMessage('tag'),
            'M0 0',
            route: 'tag_index',
            path: 'tags',
        );
        $resource = (new AdminMenuItemMapper())->map(new AdminMenuNode($item, 'tag', 'Tags', '/admin/fr/tags'));

        $this->assertNull($resource->legacyUrl);
        $this->assertSame('tag_index', $resource->route);
        $this->assertSame('tags', $resource->path);
    }
}
