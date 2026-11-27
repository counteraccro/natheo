<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de GET /admin/tags
 */

namespace App\Tests\Api\V2\Admin\Content\Tag;

use App\Api\V2\Resource\Admin\Content\AdminTag;
use Doctrine\DBAL\Connection;

class AdminTagGetCollectionTest extends AdminTagTestCase
{
    /**
     * Libellé de la langue de contenu, auteur, nombre de pages et droits
     * @return void
     */
    public function testCollection(): void
    {
        $headers = $this->contributorHeaders($user);
        $mine = $this->createLabelledTag($user, 'Symfony');
        $other = $this->createLabelledTag(null, 'Vue', ['disabled' => true]);
        $this->createPageTag(tag: $mine);

        $response = $this->requestV2('GET', '/admin/tags', headers: $headers);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertMatchesResourceCollectionJsonSchema(AdminTag::class);
        $content = $this->decode($response);
        $this->assertSame(2, $content['totalItems']);
        $this->assertSame([$other->getId(), $mine->getId()], array_column($content['member'], 'id'));
        $this->assertArrayNotHasKey('sql', $content);

        [$otherItem, $mineItem] = $content['member'];
        $this->assertSame(
            ['Symfony', 1, $user->getLogin()],
            [$mineItem['label'], $mineItem['pageCount'], $mineItem['author']['login']],
        );
        $this->assertArrayNotHasKey('translations', $mineItem);
        $this->assertTrue($mineItem['permissions']['delete']);
        $this->assertFalse($otherItem['permissions']['edit']);
        $this->assertTrue($otherItem['disabled']);

        $content = $this->decode($this->requestV2('GET', '/admin/tags?locale=en', headers: $headers));
        $this->assertSame('Vue en', $content['member'][0]['label']);
    }

    /**
     * Recherche sans tenir compte de la casse, tri par libellé, statut, auteur et pagination
     * @return void
     */
    public function testFilters(): void
    {
        $headers = $this->contributorHeaders($user);
        $alpha = $this->createLabelledTag($user, 'Alpha');
        $beta = $this->createLabelledTag(null, 'beta', ['disabled' => true]);
        $gamma = $this->createLabelledTag($user, 'Gamma');

        $this->assertSame([$beta->getId()], $this->ids('/admin/tags?search=BET', $headers));
        $this->assertSame([$beta->getId()], $this->ids('/admin/tags?search=TA%20ES&locale=es', $headers));
        $this->assertSame(
            [$alpha->getId(), $beta->getId(), $gamma->getId()],
            $this->ids('/admin/tags?order[label]=asc', $headers),
        );
        $this->assertSame([$beta->getId()], $this->ids('/admin/tags?status=disabled', $headers));
        $this->assertSame([$gamma->getId(), $alpha->getId()], $this->ids('/admin/tags?author=me', $headers));
        $this->assertSame([$beta->getId()], $this->ids('/admin/tags?itemsPerPage=1&page=2', $headers));
        $this->assertProblem($this->requestV2('GET', '/admin/tags?order[label]=haut', headers: $headers), 422);
    }

    /**
     * Sans traduction dans la langue demandée, la recherche et le tri portent sur le libellé de la langue par défaut
     * @return void
     */
    public function testSearchOnDefaultLabel(): void
    {
        $headers = $this->contributorHeaders($user);
        $translated = $this->createLabelledTag($user, 'Zeta');
        $untranslated = $this->createTag($user, ['color' => '#112233', 'disabled' => false]);
        $this->createTagTranslation($untranslated, ['locale' => 'fr', 'label' => 'Omega']);

        $this->assertSame([$untranslated->getId()], $this->ids('/admin/tags?search=omeg&locale=en', $headers));
        $this->assertSame(
            [$untranslated->getId(), $translated->getId()],
            $this->ids('/admin/tags?order[label]=asc&locale=en', $headers),
        );
    }

    /**
     * La requête SQL renvoyée au super administrateur s'exécute sur la base et rend les mêmes tags, dans le même
     * ordre, que la réponse
     * @return void
     */
    public function testSqlQuery(): void
    {
        $user = $this->createUserSuperAdmin(['disabled' => false, 'anonymous' => false]);
        $headers = $this->adminSessionHeaders($user);
        $tags = [
            $this->createLabelledTag($user, "L'été ? 100% \\ a_b"),
            $this->createLabelledTag($user, "l'été ? 100% \\ a_b bis"),
            $this->createLabelledTag(null, "L'été ? 100% \\ a_b ter", ['disabled' => true]),
            $this->createLabelledTag($user, 'Autre'),
        ];
        $connection = $this->container->get(Connection::class);

        foreach (
            [
                '/admin/tags?sql=true',
                '/admin/tags?sql=true&search=' . rawurlencode("L'ÉTÉ ? 100% \\ A_B") . '&order[label]=desc',
                '/admin/tags?sql=true&status=enabled&author=me&order[label]=asc&itemsPerPage=1&page=2',
                '/admin/tags?sql=true&locale=es&search=bis',
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
        $this->assertCount(4, $tags);
    }

    /**
     * Valeur de la première colonne id_* d'une ligne, celle du tag dans la requête générée par Doctrine
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

    /**
     * Identifiants des tags d'une collection
     * @param string $path
     * @param array $headers
     * @return int[]
     */
    private function ids(string $path, array $headers): array
    {
        $response = $this->requestV2('GET', $path, headers: $headers);
        $this->assertEquals(200, $response->getStatusCode(), $path);
        return array_column($this->decode($response)['member'], 'id');
    }
}
