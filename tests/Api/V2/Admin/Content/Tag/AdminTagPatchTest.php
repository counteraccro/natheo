<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de PATCH /admin/tags/{id}
 */

namespace App\Tests\Api\V2\Admin\Content\Tag;

use App\Api\V2\Resource\Admin\Content\AdminTag;

class AdminTagPatchTest extends AdminTagTestCase
{
    /**
     * Modification partielle : une langue absente garde son libellé, le tag peut garder son libellé à la casse près
     * @return void
     */
    public function testPatch(): void
    {
        $headers = $this->contributorHeaders($user);
        $tag = $this->createLabelledTag($user, 'Symfony');

        $response = $this->requestV2(
            'PATCH',
            '/admin/tags/' . $tag->getId(),
            body: [
                'color' => '#abcdef',
                'translations' => [['locale' => 'fr', 'label' => 'SYMFONY']],
            ],
            headers: $headers,
        );
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertMatchesResourceItemJsonSchema(AdminTag::class);
        $content = $this->decode($response);
        $this->assertSame('#abcdef', $content['color']);
        $this->assertSame(['SYMFONY', 'Symfony en', 'Symfony es'], array_column($content['translations'], 'label'));
        $this->assertSame([['update', (string) $tag->getId(), 'SYMFONY']], $this->logs());

        $this->createLabelledTag(null, 'Vue');
        $content = $this->assertProblem(
            $this->requestV2(
                'PATCH',
                '/admin/tags/' . $tag->getId(),
                body: [
                    'translations' => [['locale' => 'fr', 'label' => 'vue']],
                ],
                headers: $headers,
            ),
            422,
        );
        $this->assertSame('translations[0].label', $content['violations'][0]['propertyPath']);
    }

    /**
     * Le statut seul est tracé disable / enable
     * @return void
     */
    public function testStatus(): void
    {
        $headers = $this->contributorHeaders($user);
        $tag = $this->createLabelledTag($user, 'Symfony');
        $path = '/admin/tags/' . $tag->getId();

        $this->assertTrue(
            $this->decode($this->requestV2('PATCH', $path, body: ['disabled' => true], headers: $headers))['disabled'],
        );
        $this->assertFalse(
            $this->decode($this->requestV2('PATCH', $path, body: ['disabled' => false], headers: $headers))['disabled'],
        );
        $id = (string) $tag->getId();
        $this->assertSame([['disable', $id, 'Symfony'], ['enable', $id, 'Symfony']], $this->logs());
    }

    /**
     * Un contributeur ne modifie pas le tag d'un autre, même sans effet, un administrateur modifie tout ; 404 avant 403
     * @return void
     */
    public function testRights(): void
    {
        $tag = $this->createLabelledTag(null, 'Symfony');
        $path = '/admin/tags/' . $tag->getId();
        $headers = $this->contributorHeaders();

        $content = $this->assertProblem(
            $this->requestV2('PATCH', $path, body: ['color' => '#000000'], headers: $headers),
            403,
        );
        $this->assertSame($this->translator->trans('tag.error.forbidden', domain: 'tag'), $content['detail']);
        $this->assertProblem($this->requestV2('PATCH', $path, body: ['disabled' => true], headers: $headers), 403);
        $this->assertProblem($this->requestV2('PATCH', $path, body: ['disabled' => false], headers: $headers), 403);
        $this->assertProblem($this->requestV2('PATCH', $path, body: [], headers: $headers), 403);
        $this->assertProblem($this->requestV2('PATCH', '/admin/tags/999999', body: [], headers: $headers), 404);

        $response = $this->requestV2('PATCH', $path, body: ['disabled' => true], headers: $this->adminHeaders());
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Un If-Match périmé donne 412, l'ETag change à chaque écriture même dans la même seconde
     * @return void
     */
    public function testConcurrency(): void
    {
        $headers = $this->contributorHeaders($user);
        $path = '/admin/tags/' . $this->createLabelledTag($user, 'Symfony')->getId();
        $etag = $this->requestV2('GET', $path, headers: $headers)->headers->get('ETag');

        $response = $this->requestV2(
            'PATCH',
            $path,
            body: ['color' => '#000001'],
            headers: ['HTTP_IF_MATCH' => $etag, ...$headers],
        );
        $this->assertEquals(200, $response->getStatusCode());
        $newEtag = $response->headers->get('ETag');
        $this->assertNotSame($etag, $newEtag);

        $this->assertProblem(
            $this->requestV2(
                'PATCH',
                $path,
                body: ['color' => '#000002'],
                headers: ['HTTP_IF_MATCH' => $etag, ...$headers],
            ),
            412,
        );
        $response = $this->requestV2(
            'PATCH',
            $path,
            body: ['color' => '#000003'],
            headers: ['HTTP_IF_MATCH' => $newEtag, ...$headers],
        );
        $this->assertEquals(200, $response->getStatusCode());
    }
}
