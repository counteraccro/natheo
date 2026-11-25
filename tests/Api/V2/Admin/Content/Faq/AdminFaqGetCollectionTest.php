<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de GET /admin/faqs
 */

namespace App\Tests\Api\V2\Admin\Content\Faq;

use App\Api\V2\Resource\Admin\Content\AdminFaq;
use Doctrine\DBAL\Connection;

class AdminFaqGetCollectionTest extends AdminFaqTestCase
{
    /**
     * Titre de la langue de contenu, compteurs, auteur et droits, sans l'arbre
     * @return void
     */
    public function testCollection(): void
    {
        $headers = $this->contributorHeaders($user);
        $mine = $this->createTitledFaq($user, 'Produit', ['Achat' => ['Q1', 'Q2'], 'Livraison' => ['Q3']]);
        $other = $this->createTitledFaq(null, 'Support', [], ['disabled' => true]);
        $this->usePage($mine);
        $this->usePage($mine);

        $response = $this->requestV2('GET', '/admin/faqs', headers: $headers);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertMatchesResourceCollectionJsonSchema(AdminFaq::class);
        $content = $this->decode($response);
        $this->assertSame(2, $content['totalItems']);
        $this->assertSame([$other->getId(), $mine->getId()], array_column($content['member'], 'id'));

        [$otherItem, $mineItem] = $content['member'];
        $this->assertSame(
            ['Produit', 2, 3, 2, $user->getLogin()],
            [
                $mineItem['title'],
                $mineItem['categoryCount'],
                $mineItem['questionCount'],
                $mineItem['pageCount'],
                $mineItem['author']['login'],
            ],
        );
        $this->assertSame(
            [0, 0, 0],
            [$otherItem['categoryCount'], $otherItem['questionCount'], $otherItem['pageCount']],
        );
        $this->assertArrayNotHasKey('categories', $mineItem);
        $this->assertArrayNotHasKey('translations', $mineItem);
        $this->assertTrue($mineItem['permissions']['edit']);
        $this->assertFalse($otherItem['permissions']['edit']);
        $this->assertTrue($otherItem['disabled']);

        $content = $this->decode($this->requestV2('GET', '/admin/faqs?locale=en', headers: $headers));
        $this->assertSame('Support en', $content['member'][0]['title']);
    }

    /**
     * Recherche sans tenir compte de la casse, tri par titre, statut, auteur et pagination
     * @return void
     */
    public function testFilters(): void
    {
        $headers = $this->contributorHeaders($user);
        $alpha = $this->createTitledFaq($user, 'Alpha');
        $beta = $this->createTitledFaq(null, 'beta', [], ['disabled' => true]);
        $gamma = $this->createTitledFaq($user, 'Gamma');

        $this->assertSame([$beta->getId()], $this->ids('/admin/faqs?search=BET', $headers));
        $this->assertSame([$beta->getId()], $this->ids('/admin/faqs?search=TA%20ES&locale=es', $headers));
        $this->assertSame(
            [$alpha->getId(), $beta->getId(), $gamma->getId()],
            $this->ids('/admin/faqs?order[title]=asc', $headers),
        );
        $this->assertSame([$beta->getId()], $this->ids('/admin/faqs?status=disabled', $headers));
        $this->assertSame([$gamma->getId(), $alpha->getId()], $this->ids('/admin/faqs?author=me', $headers));
        $this->assertSame([$beta->getId()], $this->ids('/admin/faqs?itemsPerPage=1&page=2', $headers));
        $this->assertProblem($this->requestV2('GET', '/admin/faqs?order[title]=haut', headers: $headers), 422);
    }

    /**
     * La requête SQL renvoyée au super administrateur s'exécute sur la base et rend les mêmes FAQ, dans le même
     * ordre, que la réponse
     * @return void
     */
    public function testSqlQuery(): void
    {
        $user = $this->createUserSuperAdmin(['disabled' => false, 'anonymous' => false]);
        $headers = $this->adminSessionHeaders($user);
        $used = $this->createTitledFaq($user, "L'été ? 100% \\ a_b", ['Cat' => ['Q']]);
        $this->usePage($used);
        $this->createTitledFaq($user, "l'été ? 100% \\ a_b bis");
        $this->createTitledFaq(null, "L'été ? 100% \\ a_b ter", [], ['disabled' => true]);
        $this->createTitledFaq($user, 'Autre');
        $connection = $this->container->get(Connection::class);

        foreach (
            [
                '/admin/faqs?sql=true',
                '/admin/faqs?sql=true&search=' . rawurlencode("L'ÉTÉ ? 100% \\ A_B") . '&order[title]=desc',
                '/admin/faqs?sql=true&status=enabled&author=me&order[title]=asc&itemsPerPage=1&page=2',
                '/admin/faqs?sql=true&locale=es&search=bis',
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
     * Valeur de la première colonne id_* d'une ligne, celle de la FAQ dans la requête générée par Doctrine
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
