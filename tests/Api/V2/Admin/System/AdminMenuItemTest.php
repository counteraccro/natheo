<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests des ressources AdminMenuItem : entrées du menu visibles selon le rôle de l'utilisateur de la session
 */

namespace App\Tests\Api\V2\Admin\System;

use App\Api\V2\Resource\Admin\System\AdminMenuItem;
use App\Enum\Admin\Comment\CommentStatus;
use App\Tests\Api\V2\AppApiV2TestCase;

class AdminMenuItemTest extends AppApiV2TestCase
{
    /**
     * Refus sans session et avec une session ouverte par un token public
     * @return void
     */
    public function testAccess(): void
    {
        $user = $this->createUserContributeur(['disabled' => false, 'anonymous' => false]);
        $publicSession = $this->sessionHeaders($user);

        $this->assertProblem($this->requestV2('GET', '/admin/menu_items', self::HEADER_NONE), 401);
        $this->assertProblem($this->requestV2('GET', '/admin/menu_items', headers: $publicSession), 403);
    }

    /**
     * Liste plate dans l'ordre du menu, filtrée par rôle, avec adresses, raccourcis et badges
     * @return void
     */
    public function testCollection(): void
    {
        $this->createComment(customData: ['status' => CommentStatus::WAIT_VALIDATION->value]);
        $headers = $this->adminSessionHeaders(
            $this->createUserContributeur(['disabled' => false, 'anonymous' => false]),
        );

        $response = $this->requestV2('GET', '/admin/menu_items', headers: $headers);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertMatchesResourceCollectionJsonSchema(AdminMenuItem::class);
        $items = array_column($this->decode($response)['member'], null, 'id');

        $this->assertSame(
            ['dashboard', 'content', 'menu', 'menu_index', 'menu_new'],
            array_slice(array_keys($items), 0, 5),
        );
        $this->assertArrayNotHasKey('system', $items);
        $this->assertArrayNotHasKey('tools', $items);

        $this->assertNull($items['content']['parent']);
        $this->assertNull($items['content']['module']);
        $this->assertNull($items['content']['legacyUrl']);
        $this->assertSame('page', $items['page_new']['parent']);
        $this->assertSame('page', $items['page_new']['module']);
        $this->assertSame($this->router->generate('admin_page_add'), $items['page_new']['legacyUrl']);
        $this->assertTrue($items['page_new']['shortcut']);
        $this->assertFalse($items['page_new']['hidden']);
        $this->assertSame(1, $items['comment']['badge']);
        $this->assertSame(1, $items['comment_moderate']['badge']);
        $this->assertNull($items['comment_index']['badge']);
    }

    /**
     * Les entrées des tokens mènent à la grille avec le type, une entrée invisible n'est pas lisible
     * @return void
     */
    public function testItem(): void
    {
        $admin = $this->adminSessionHeaders($this->createUserAdmin(['disabled' => false, 'anonymous' => false]));
        $contributor = $this->adminSessionHeaders(
            $this->createUserContributeur(['disabled' => false, 'anonymous' => false]),
        );
        $response = $this->requestV2('GET', '/admin/menu_items/api_token_public', headers: $admin);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertMatchesResourceItemJsonSchema(AdminMenuItem::class);
        $this->assertSame(
            $this->router->generate('admin_api_token_index', ['scope' => 'public']),
            $this->decode($response)['legacyUrl'],
        );
        $this->assertProblem($this->requestV2('GET', '/admin/menu_items/user', headers: $admin), 404);
        $this->assertProblem($this->requestV2('GET', '/admin/menu_items/api_token_public', headers: $contributor), 404);
    }
}
