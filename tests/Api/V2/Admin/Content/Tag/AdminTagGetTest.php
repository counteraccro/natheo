<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de GET /admin/tags/{id}
 */

namespace App\Tests\Api\V2\Admin\Content\Tag;

use App\Api\V2\Resource\Admin\Content\AdminTag;

class AdminTagGetTest extends AdminTagTestCase
{
    /**
     * Libellés de chaque langue dans l'ordre du site, ETag, 404
     * @return void
     */
    public function testGet(): void
    {
        $headers = $this->contributorHeaders();
        $tag = $this->createLabelledTag(null, 'Symfony');

        $response = $this->requestV2('GET', '/admin/tags/' . $tag->getId() . '?locale=es', headers: $headers);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertMatchesResourceItemJsonSchema(AdminTag::class);
        $this->assertNotNull($response->headers->get('ETag'));
        $content = $this->decode($response);
        $this->assertSame('Symfony es', $content['label']);
        $this->assertSame(
            [['fr', 'Symfony'], ['en', 'Symfony en'], ['es', 'Symfony es']],
            array_map(fn(array $t) => [$t['locale'], $t['label']], $content['translations']),
        );
        $this->assertSame(0, $content['pageCount']);

        $content = $this->assertProblem($this->requestV2('GET', '/admin/tags/999999', headers: $headers), 404);
        $this->assertSame($this->translator->trans('tag.error.not_found', domain: 'tag'), $content['detail']);
    }

    /**
     * Session back-office et rôle contributeur exigés
     * @return void
     */
    public function testAccess(): void
    {
        $path = '/admin/tags/' . $this->createLabelledTag(null, 'Symfony')->getId();

        $this->assertEquals(401, $this->requestV2('GET', $path, self::HEADER_NONE)->getStatusCode());
        $this->assertProblem($this->requestV2('GET', $path), 403);
        $this->assertProblem($this->requestV2('GET', $path, headers: $this->backOfficeTokenHeaders()), 403);
        $user = $this->createUser(['disabled' => false, 'anonymous' => false, 'roles' => ['ROLE_USER']]);
        $this->assertProblem($this->requestV2('GET', $path, headers: $this->adminSessionHeaders($user)), 403);
    }
}
