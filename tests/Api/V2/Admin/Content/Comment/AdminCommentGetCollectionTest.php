<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de GET /admin/comments
 */

namespace App\Tests\Api\V2\Admin\Content\Comment;

use App\Api\V2\Resource\Admin\Content\AdminComment;
use App\Enum\Admin\Comment\CommentStatus;
use Doctrine\DBAL\Connection;

class AdminCommentGetCollectionTest extends AdminCommentTestCase
{
    /**
     * Extrait sans Markdown, page dans la langue de contenu, modérateur et droits
     * @return void
     */
    public function testCollection(): void
    {
        $headers = $this->contributorHeaders($user);
        $page = $this->createTitledPage('Nathéo 2.0');
        $long = $this->createComment($page, customData: [
            'comment' => '**Bravo** pour [cette version](https://example.com). ' . str_repeat('Très bien. ', 20),
            'status' => CommentStatus::VALIDATE->value,
        ]);
        $moderated = $this->createComment($page, $user, [
            'status' => CommentStatus::MODERATE->value,
            'moderationComment' => 'Publicité',
        ]);

        $response = $this->requestV2('GET', '/admin/comments', headers: $headers);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertMatchesResourceCollectionJsonSchema(AdminComment::class);
        $content = $this->decode($response);
        $this->assertSame(2, $content['totalItems']);
        $this->assertSame([$moderated->getId(), $long->getId()], array_column($content['member'], 'id'));
        $this->assertArrayNotHasKey('sql', $content);

        [$moderatedItem, $longItem] = $content['member'];
        $this->assertStringStartsWith('Bravo pour cette version. Très bien.', $longItem['excerpt']);
        $this->assertSame(100, mb_strlen($longItem['excerpt']));
        $this->assertStringEndsWith('…', $longItem['excerpt']);
        $this->assertSame('validate', $longItem['status']);
        $this->assertNull($longItem['moderator']);
        $this->assertSame(
            [$page->getId(), 'Nathéo 2.0', null],
            [$longItem['page']['id'], $longItem['page']['title'], $longItem['page']['url']],
        );
        $this->assertSame(
            [true, false, false],
            [$longItem['permissions']['edit'], $longItem['permissions']['disable'], $longItem['permissions']['delete']],
        );
        $this->assertArrayNotHasKey('comment', $longItem);
        $this->assertArrayNotHasKey('ip', $longItem);
        $this->assertSame($user->getLogin(), $moderatedItem['moderator']['login']);

        $content = $this->decode($this->requestV2('GET', '/admin/comments?locale=en', headers: $headers));
        $this->assertSame('Nathéo 2.0 en', $content['member'][0]['page']['title']);
    }

    /**
     * Recherche, statut, page, modérateur, tri par page dans la langue de contenu et pagination
     * @return void
     */
    public function testFilters(): void
    {
        $headers = $this->contributorHeaders($user);
        $alpha = $this->createTitledPage('Alpha');
        $beta = $this->createTitledPage('Beta');
        $first = $this->createComment($beta, customData: [
            'author' => 'Camille',
            'email' => 'camille@example.com',
            'comment' => 'Merci !',
        ]);
        $second = $this->createComment($alpha, $user, [
            'author' => 'Promo',
            'email' => 'spam@example.com',
            'comment' => 'Cliquez ici',
            'status' => CommentStatus::MODERATE->value,
        ]);
        $third = $this->createComment($alpha, customData: [
            'author' => 'Daniel',
            'email' => 'daniel@example.com',
            'comment' => 'Great release',
            'status' => CommentStatus::VALIDATE->value,
        ]);

        $this->assertSame([$first->getId()], $this->ids('/admin/comments?search=CAMI', $headers));
        $this->assertSame([$first->getId()], $this->ids('/admin/comments?search=merci', $headers));
        $this->assertSame([$second->getId()], $this->ids('/admin/comments?search=SPAM@', $headers));
        $this->assertSame([$second->getId()], $this->ids('/admin/comments?moderation=moderate', $headers));
        $this->assertSame([$first->getId()], $this->ids('/admin/comments?moderation=wait_validation', $headers));
        $this->assertSame(
            [$third->getId(), $second->getId()],
            $this->ids('/admin/comments?pageId=' . $alpha->getId(), $headers),
        );
        $this->assertSame([$second->getId()], $this->ids('/admin/comments?author=me', $headers));
        $byPage = $this->ids('/admin/comments?order[page]=asc', $headers);
        $this->assertEqualsCanonicalizing([$second->getId(), $third->getId()], array_slice($byPage, 0, 2));
        $this->assertSame($first->getId(), $byPage[2]);
        $this->assertSame([$second->getId()], $this->ids('/admin/comments?itemsPerPage=1&page=2', $headers));
        $this->assertProblem($this->requestV2('GET', '/admin/comments?moderation=spam', headers: $headers), 422);
        $this->assertProblem($this->requestV2('GET', '/admin/comments?pageId=abc', headers: $headers), 422);
    }

    /**
     * Une page traduite dans plusieurs langues ne multiplie pas ses commentaires ; sans traduction dans la langue
     * demandée, le titre est celui de la langue par défaut
     * @return void
     */
    public function testPageTranslations(): void
    {
        $headers = $this->contributorHeaders();
        $translated = $this->createComment($this->createTitledPage('Zeta'));
        $untranslated = $this->createComment($this->createTitledPage('Omega', ['fr']));

        $content = $this->decode($this->requestV2('GET', '/admin/comments?order[page]=asc&locale=en', headers: $headers));
        $this->assertSame(2, $content['totalItems']);
        $this->assertSame(
            [[$untranslated->getId(), 'Omega'], [$translated->getId(), 'Zeta en']],
            array_map(fn(array $item) => [$item['id'], $item['page']['title']], $content['member']),
        );
    }

    /**
     * La requête SQL renvoyée au super administrateur s'exécute sur la base et rend les mêmes commentaires, dans le
     * même ordre, que la réponse
     * @return void
     */
    public function testSqlQuery(): void
    {
        $user = $this->createUserSuperAdmin(['disabled' => false, 'anonymous' => false]);
        $headers = $this->adminSessionHeaders($user);
        $page = $this->createTitledPage("L'été ? 100%");
        $this->createComment($page, customData: ['author' => "L'été \\ a_b"]);
        $this->createComment($page, $user, ['status' => CommentStatus::MODERATE->value]);
        $this->createComment($this->createTitledPage('Autre', ['fr']), customData: ['status' => CommentStatus::VALIDATE->value]);
        $connection = $this->container->get(Connection::class);

        foreach (
            [
                '/admin/comments?sql=true',
                '/admin/comments?sql=true&search=' . rawurlencode("L'ÉTÉ \\ A_B") . '&order[page]=desc',
                '/admin/comments?sql=true&moderation=moderate&author=me&pageId=' . $page->getId(),
                '/admin/comments?sql=true&locale=es&order[page]=asc&itemsPerPage=2&page=1',
            ]
            as $path
        ) {
            $response = $this->requestV2('GET', $path, headers: $headers);
            $this->assertEquals(200, $response->getStatusCode(), $path);
            $content = $this->decode($response);
            $this->assertNotSame([], $content['member'], $path);
            $this->assertStringNotContainsString('password', $content['sql'], $path);
            $rows = $connection->executeQuery($content['sql'])->fetchAllAssociative();
            $this->assertSame(
                array_column($content['member'], 'id'),
                array_map(fn(array $row) => $this->firstId($row), $rows),
                $path . "\n" . $content['sql'],
            );
        }
    }

    /**
     * Valeur de la première colonne id_* d'une ligne, celle du commentaire dans la requête générée par Doctrine
     * @param array<string, mixed> $row
     * @return int
     */
    private function firstId(array $row): int
    {
        foreach ($row as $column => $value) {
            if (str_starts_with($column, 'id_')) {
                return (int) $value;
            }
        }
        return 0;
    }
}
