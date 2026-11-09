<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de la ressource Menu de l'API v2
 */

namespace App\Tests\Api\V2\Content;

use App\Api\V2\Resource\Content\Menu as MenuResource;
use App\Enum\Admin\Content\Menu\MenuPosition;
use App\Tests\Api\V2\AppApiV2TestCase;

class MenuTest extends AppApiV2TestCase
{
    /**
     * Menu par son id : éléments actifs et enfants
     * @return void
     */
    public function testGetById(): void
    {
        $menu = $this->createMenuAllDataDefault();
        $disabled = $this->createMenuAllDataDefault();
        $disabled->setDisabled(true);
        $this->persistAndFlush($disabled);

        $content = $this->decode($this->requestV2('GET', '/menus/' . $menu->getId() . '?locale=fr'));
        $this->assertMatchesResourceItemJsonSchema(MenuResource::class);
        $this->assertEquals('/api/v2/menus/' . $menu->getId(), $content['@id']);
        $this->assertEquals(MenuPosition::from($menu->getPosition())->getApiName(), $content['position']);

        // 4 éléments de premier niveau dont 1 désactivé, un enfant sous le 3e
        $this->assertCount(3, $content['elements']);
        $this->assertEquals('enfant', $content['elements'][2]['children'][0]['label']);
        $this->assertEquals(
            ['@type', 'id', 'label', 'target', 'url', 'pageSlug', 'pageCategory', 'column', 'row', 'children'],
            array_keys($content['elements'][0]),
        );

        $this->assertProblem($this->requestV2('GET', '/menus/' . $disabled->getId()), 404);
        $this->assertProblem($this->requestV2('GET', '/menus/999999999'), 404);
    }

    /**
     * Menus d'une page, filtrés par position
     * @return void
     */
    public function testCollectionByPage(): void
    {
        $page = $this->createPageAllDataDefault();
        $menu = $this->createMenuAllDataDefault();
        $page->addMenu($menu);
        $this->persistAndFlush($page);
        $slug = $page->getPageTranslationByLocale('fr')->getUrl();
        $position = MenuPosition::from($menu->getPosition())->getApiName();

        $content = $this->decode($this->requestV2('GET', '/menus?page=' . $slug));
        $this->assertMatchesResourceCollectionJsonSchema(MenuResource::class);
        $this->assertContains($menu->getId(), array_column($content['member'], 'id'));

        $content = $this->decode($this->requestV2('GET', '/menus?page=' . $slug . '&position=' . $position));
        foreach ($content['member'] as $member) {
            $this->assertEquals($position, $member['position']);
        }
        $this->assertContains($menu->getId(), array_column($content['member'], 'id'));

        $this->assertProblem($this->requestV2('GET', '/menus?page=page-inexistante'), 404);
        $this->assertEquals(422, $this->requestV2('GET', '/menus')->getStatusCode());
        $this->assertEquals(422, $this->requestV2('GET', '/menus?page=' . $slug . '&position=top')->getStatusCode());
    }
}
