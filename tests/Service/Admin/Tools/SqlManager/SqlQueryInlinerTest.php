<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de SqlRecorder et SqlQueryInliner : la requête écrite avec ses valeurs s'exécute sur la base en cours
 * et rend le même résultat que la requête préparée
 */

namespace App\Tests\Service\Admin\Tools\SqlManager;

use App\Entity\Admin\System\User;
use App\Service\Admin\Tools\SqlManager\RecordedQuery;
use App\Service\Admin\Tools\SqlManager\SqlQueryInliner;
use App\Service\Admin\Tools\SqlManager\SqlRecorder;
use App\Tests\AppWebTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\ORM\EntityManagerInterface;

class SqlQueryInlinerTest extends AppWebTestCase
{
    private Connection $connection;

    private SqlRecorder $recorder;

    private SqlQueryInliner $inliner;

    public function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->container->get(Connection::class);
        $this->recorder = $this->container->get(SqlRecorder::class);
        $this->inliner = new SqlQueryInliner($this->connection);
    }

    /**
     * Rien n'est gardé hors de record(), chaque requête préparée l'est pendant
     * @return void
     */
    public function testRecord(): void
    {
        $this->connection->executeQuery('SELECT ? AS v', [1]);
        [$result, $queries] = $this->recorder->record(
            fn() => $this->connection->executeQuery('SELECT ? AS v', ['a'])->fetchOne(),
        );
        $this->assertSame('a', $result);
        $this->assertCount(1, $queries);
        $this->assertSame([1 => 'a'], $queries[0]->params);
        $this->assertFalse($this->recorder->isRecording());

        $this->connection->executeQuery('SELECT 1');
        $this->assertSame([], $this->recorder->record(fn() => null)[1]);
    }

    /**
     * Chaînes à échapper, entier, booléen, null et date donnent le même résultat écrites dans la requête
     * @return void
     */
    public function testValuesByType(): void
    {
        $sql = 'SELECT ? AS s, ? AS i, ? AS b, ? AS n, ? AS d';
        $params = ["l'été \\ ? % _ \" ; --", 42, true, null, '2026-11-21 10:00:00'];
        $types = [
            ParameterType::STRING,
            ParameterType::INTEGER,
            ParameterType::BOOLEAN,
            ParameterType::NULL,
            ParameterType::STRING,
        ];

        $inlined = $this->inlineRecorded($sql, $params, $types);
        $this->assertSame(
            $this->normalize($this->connection->executeQuery($sql, $params, $types)->fetchAssociative()),
            $this->normalize($this->connection->executeQuery($inlined)->fetchAssociative()),
        );
        $this->assertStringNotContainsString('?', $this->stripStrings($inlined));
    }

    /**
     * Un « ? » dans une chaîne, un identifiant entre guillemets ou un commentaire n'est pas remplacé
     * @return void
     */
    public function testMarkersOutsideParameters(): void
    {
        $quote = $this->connection->getDatabasePlatform()->quoteSingleIdentifier('a?');
        $sql = "SELECT '?' AS q, ? AS v, 1 AS " . $quote . ' /* ? */';
        $inlined = $this->inlineRecorded($sql, ['x'], [ParameterType::STRING]);

        $this->assertSame(
            ['q' => '?', 'v' => 'x', 'a?' => 1],
            array_map(
                fn($value) => is_numeric($value) ? (int) $value : $value,
                $this->connection->executeQuery($inlined)->fetchAssociative(),
            ),
        );
        $this->assertStringContainsString('/* ? */', $inlined);
    }

    /**
     * Booléen écrit en littéral de la plateforme
     * @return void
     */
    public function testBooleanLiteral(): void
    {
        $inlined = $this->inliner->inline(new RecordedQuery('SELECT ?', [1 => 0], [1 => ParameterType::BOOLEAN]));
        $expected = $this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform ? 'false' : '0';
        $this->assertSame('SELECT ' . $expected, trim(preg_replace('/\s+/', ' ', $inlined)));
    }

    /**
     * Une requête DQL (liste, booléen, date, entité) enregistrée puis écrite rend les mêmes lignes
     * @return void
     */
    public function testDoctrineQuery(): void
    {
        $users = [$this->createUser(), $this->createUser(), $this->createUser(['disabled' => true])];
        $em = $this->container->get(EntityManagerInterface::class);
        $query = $em
            ->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->where('u.id IN (:ids)')
            ->andWhere('u.disabled = :disabled')
            ->andWhere('u.createdAt < :date')
            ->andWhere('u <> :excluded')
            ->andWhere('LOWER(u.login) NOT LIKE :search')
            ->setParameter('ids', array_map(fn(User $user) => $user->getId(), $users))
            ->setParameter('disabled', false)
            ->setParameter('date', new \DateTime('+1 day'))
            ->setParameter('excluded', $users[1])
            ->setParameter('search', "%l'a\\_%")
            ->orderBy('u.id', 'DESC')
            ->getQuery();

        [$result, $queries] = $this->recorder->record(fn() => $query->getResult());
        $this->assertSame([$users[0]->getId()], array_map(fn(User $user) => $user->getId(), $result));

        $rows = $this->connection->executeQuery($this->inliner->inline(end($queries)))->fetchAllNumeric();
        $this->assertSame([$users[0]->getId()], array_map(fn(array $row) => (int) $row[0], $rows));
    }

    /**
     * Requête préparée exécutée pendant record(), puis écrite avec ses valeurs
     * @param string $sql
     * @param array $params
     * @param ParameterType[] $types
     * @return string
     */
    private function inlineRecorded(string $sql, array $params, array $types): string
    {
        [, $queries] = $this->recorder->record(fn() => $this->connection->executeQuery($sql, $params, $types));
        $this->assertCount(1, $queries);
        return $this->inliner->inline($queries[0]);
    }

    /**
     * Valeurs d'une ligne en chaînes : PostgreSQL rend en texte les valeurs préparées d'un SELECT sans colonne
     * @param array<string, mixed> $row
     * @return array<string, string|null>
     */
    private function normalize(array $row): array
    {
        return array_map(
            fn($value) => match (true) {
                $value === null => null,
                is_bool($value), $value === 't', $value === 'f' => $value === true || $value === 't' ? '1' : '0',
                default => (string) $value,
            },
            $row,
        );
    }

    /**
     * Requête sans ses chaînes littérales
     * @param string $sql
     * @return string
     */
    private function stripStrings(string $sql): string
    {
        return (string) preg_replace("/'(?:[^'\\\\]|\\\\.|'')*'/", "''", $sql);
    }
}
