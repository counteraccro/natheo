<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de DELETE /admin/tags/{id}
 */

namespace App\Tests\Api\V2\Admin\Content\Tag;

use App\Entity\Admin\Content\Tag\Tag;

class AdminTagDeleteTest extends AdminTagTestCase
{
    /**
     * Suppression tracée, tag utilisé refusé en 409
     * @return void
     */
    public function testDelete(): void
    {
        $this->setAllowDelete(true);
        $headers = $this->contributorHeaders($user);
        $tag = $this->createLabelledTag($user, 'Symfony');
        $used = $this->createLabelledTag($user, 'Vue');
        $this->createPageTag(tag: $used);
        $this->createPageTag(tag: $used);
        $id = $tag->getId();

        $this->assertEquals(204, $this->requestV2('DELETE', '/admin/tags/' . $id, headers: $headers)->getStatusCode());
        $this->assertNull($this->em->getRepository(Tag::class)->find($id));
        $this->assertSame([['delete', (string) $id, 'Symfony']], $this->logs());

        $content = $this->assertProblem(
            $this->requestV2('DELETE', '/admin/tags/' . $used->getId(), headers: $headers),
            409,
        );
        $this->assertSame($this->translator->trans('tag.error.delete_used', ['count' => 2], 'tag'), $content['detail']);
        $this->assertCount(1, $this->logs());
    }

    /**
     * Tag d'un autre, suppression désactivée par l'option système, If-Match périmé
     * @return void
     */
    public function testRefused(): void
    {
        $this->setAllowDelete(true);
        $headers = $this->contributorHeaders($user);
        $other = $this->createLabelledTag(null, 'Symfony');
        $mine = $this->createLabelledTag($user, 'Vue');

        $this->assertProblem($this->requestV2('DELETE', '/admin/tags/' . $other->getId(), headers: $headers), 403);
        $this->assertProblem(
            $this->requestV2(
                'DELETE',
                '/admin/tags/' . $mine->getId(),
                headers: ['HTTP_IF_MATCH' => '"perime"', ...$headers],
            ),
            412,
        );
        $this->setAllowDelete(false);
        $this->assertProblem(
            $this->requestV2('DELETE', '/admin/tags/' . $mine->getId(), headers: $this->adminHeaders()),
            403,
        );
        $this->assertSame([], $this->logs());
    }
}
