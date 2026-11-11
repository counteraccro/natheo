<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests des valeurs d'enum documentées dans l'OpenAPI (propriétés et filtres)
 */

namespace App\Tests\Api\V2\Metadata;

use ApiPlatform\Metadata\QueryParameter;
use App\Api\V2\Filter\MenuPositionFilter;
use App\Api\V2\Filter\PageCategoryFilter;
use App\Api\V2\Resource\Content\Menu;
use App\Api\V2\Resource\Content\Page;
use App\Api\V2\Resource\Content\PageContent;
use App\Enum\Admin\Content\Menu\MenuPosition;
use App\Enum\Admin\Content\Menu\MenuType;
use App\Enum\Admin\Content\Page\PageCategory;
use App\Enum\Admin\Content\Page\PageContentType;
use App\Enum\Admin\Content\Page\PageRender;
use App\Tests\Api\V2\AppApiV2TestCase;

class EnumSlugSchemaTest extends AppApiV2TestCase
{
    /**
     * Chaque propriété d'enum liste les slugs de son enum
     * @return void
     */
    public function testPropertySchemas(): void
    {
        $factory = $this->container->get('api_platform.metadata.property.metadata_factory');
        $properties = [
            [Page::class, 'category', PageCategory::getSlugs()],
            [Page::class, 'render', PageRender::getSlugs()],
            [Menu::class, 'position', MenuPosition::getSlugs()],
            [Menu::class, 'type', MenuType::getSlugs()],
            [PageContent::class, 'type', PageContentType::getSlugs()],
        ];
        foreach ($properties as [$class, $property, $slugs]) {
            $this->assertEquals(
                $slugs,
                $factory->create($class, $property)->getSchema()['enum'],
                $class . '::' . $property,
            );
        }
    }

    /**
     * Les filtres à valeurs fixes listent les slugs de leur enum
     * @return void
     */
    public function testFilterSchemas(): void
    {
        $parameter = new QueryParameter();
        $this->assertEquals(PageCategory::getSlugs(), (new PageCategoryFilter())->getSchema($parameter)['enum']);
        $this->assertEquals(MenuPosition::getSlugs(), (new MenuPositionFilter())->getSchema($parameter)['enum']);
    }
}
