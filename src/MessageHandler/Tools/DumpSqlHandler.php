<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Gestionnaire de Message qui permet de créer un dump SQL en fonction des options
 */

namespace App\MessageHandler\Tools;

use App\Entity\Admin\System\User;
use App\Enum\Admin\Global\Notification\Notification;
use App\Enum\Admin\Tools\DatabaseManager\DatabaseManagerData;
use App\Message\Tools\DumpSql;
use App\Service\Admin\NotificationService;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\Table;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsMessageHandler]
class DumpSqlHandler
{
    /**
     * Nombre de lignes par requête INSERT
     */
    private const int INSERT_BATCH_SIZE = 500;

    public function __construct(
        private NotificationService $notificationService,
        private EntityManagerInterface $entityManager,
        private ParameterBagInterface $parameterBag,
        private UrlGeneratorInterface $router,
        private LoggerInterface $logger,
    ) {}

    /**
     * @param DumpSql $dumpSql
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(DumpSql $dumpSql): void
    {
        $options = $dumpSql->getOptions();

        $name = (string) ($options['filename'] ?? '');
        if (!DatabaseManagerData::isValidName($name)) {
            $name = DatabaseManagerData::FILE_NAME_DUMP->value . date('d-m-Y-H-i-s');
        }
        $directory = $this->parameterBag->get('app.dump_directory');
        $fileName = $this->getAvailableFileName($directory, $name);
        $path = $directory . $fileName;

        $user = $this->notificationService->findOneById(User::class, $dumpSql->getUserId());

        try {
            $this->writeDump($path, $options);
        } catch (\Throwable $e) {
            $this->logger->error('Echec de la génération du dump SQL ' . $fileName . ' : ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            $this->notificationService->add($user, Notification::DUMP_SQL_ERROR->value, ['file' => $fileName]);
            return;
        }

        $this->notificationService->add($user, Notification::DUMP_SQL->value, [
            'file' => $fileName,
            'url' => $this->router->generate('admin_database_manager_download_dump_file', [
                '_locale' => $dumpSql->getLocale() ?? $this->parameterBag->get('app.default_locale'),
                'filename' => $fileName,
            ]),
        ]);
    }

    /**
     * Retourne un nom de fichier libre, suffixé par -2, -3... si un dump porte déjà ce nom
     * @param string $directory
     * @param string $name nom sans extension
     * @return string nom avec extension
     */
    private function getAvailableFileName(string $directory, string $name): string
    {
        $extension = DatabaseManagerData::FILE_DUMP_EXTENSION->value;
        $candidate = $name;
        $i = 1;
        while (is_file($directory . $candidate . $extension)) {
            $suffix = '-' . ++$i;
            // Le nom suffixé doit rester valide pour pouvoir être téléchargé et supprimé
            $candidate = substr($name, 0, 100 - strlen($suffix)) . $suffix;
        }
        return $candidate . $extension;
    }

    /**
     * Écrit le dump SQL dans un nouveau fichier, supprimé en cas d'erreur
     * @param string $path
     * @param array $options
     * @return void
     * @throws Exception
     */
    private function writeDump(string $path, array $options): void
    {
        $connection = $this->entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();
        $isMysql = $platform instanceof AbstractMySQLPlatform;
        $data = $options['data'] ?? DatabaseManagerData::DATA_TABLE_AND_DATA->value;
        $withTables = in_array(
            $data,
            [DatabaseManagerData::DATA_TABLE->value, DatabaseManagerData::DATA_TABLE_AND_DATA->value],
            true,
        );
        $withData = in_array(
            $data,
            [DatabaseManagerData::DATA_DATA->value, DatabaseManagerData::DATA_TABLE_AND_DATA->value],
            true,
        );
        $tables = $this->sortTablesByDependencies($this->getListeTable($options));
        $foreignKeys = $this->getForeignKeysSQL($tables, $platform);

        // Postgres ne permet pas de désactiver les contraintes sans droits superuser, elles sont recréées après
        $dropForeignKeys = !$isMysql && $withData && !$withTables;

        (new Filesystem())->mkdir(dirname($path));
        // Mode 'x' : échoue si le fichier existe déjà, pour ne jamais écraser un autre dump
        $handle = @fopen($path, 'x');
        if ($handle === false) {
            throw new IOException('Impossible de créer le fichier ' . $path, path: $path);
        }

        try {
            if ($isMysql) {
                $this->write($handle, 'SET FOREIGN_KEY_CHECKS = 0;');
            }

            if ($withTables) {
                // getCreateTablesSQL termine par l'ajout des clés étrangères, elles sont écrites après les données
                $createTables = $platform->getCreateTablesSQL($tables);
                foreach (
                    array_slice($createTables, 0, count($createTables) - count($foreignKeys['create']))
                    as $query
                ) {
                    $this->write($handle, $query . ';');
                }
            }

            if ($dropForeignKeys) {
                foreach ($foreignKeys['drop'] as $query) {
                    $this->write($handle, $query . ';');
                }
            }

            if ($withData) {
                $this->write($handle, '/* DATA GENERATION */');
                foreach ($tables as $table) {
                    $this->writeInsertQueries($handle, $connection, $table);
                }
            }

            if ($withTables || $dropForeignKeys) {
                foreach ($foreignKeys['create'] as $query) {
                    $this->write($handle, $query . ';');
                }
            }

            if ($isMysql) {
                $this->write($handle, 'SET FOREIGN_KEY_CHECKS = 1;');
            }
        } catch (\Throwable $e) {
            fclose($handle);
            $handle = null;
            (new Filesystem())->remove($path);
            throw $e;
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
        }
    }

    /**
     * Retourne les requêtes de suppression ('drop') et de création ('create') des clés étrangères des tables
     * @param Table[] $tables
     * @param AbstractPlatform $platform
     * @return array{drop: string[], create: string[]}
     */
    private function getForeignKeysSQL(array $tables, AbstractPlatform $platform): array
    {
        $sql = ['drop' => [], 'create' => []];
        foreach ($tables as $table) {
            $tableName = $table->getObjectName()->toSQL($platform);
            foreach ($table->getForeignKeys() as $foreignKey) {
                $sql['create'][] = $platform->getCreateForeignKeySQL($foreignKey, $tableName);
                $name = $foreignKey->getObjectName();
                if ($name !== null) {
                    $sql['drop'][] = $platform->getDropForeignKeySQL($name->toSQL($platform), $tableName);
                }
            }
        }
        return $sql;
    }

    /**
     * Retourne la liste des tables pour la sauvegarde en fonction des options
     * @param array $options
     * @return Table[]
     * @throws Exception
     */
    private function getListeTable(array $options): array
    {
        $schemaParam = $this->parameterBag->get('app.default_database_name');
        $schema = $this->entityManager->getConnection()->createSchemaManager();
        $tablesTmp = $schema->introspectSchema()->getTables();

        if ($options['all'] ?? true) {
            return $tablesTmp;
        }

        $tables = [];
        foreach ($tablesTmp as $table) {
            $name = $table->getObjectName()->toString();
            foreach ($options['tables'] ?? [] as $tDump) {
                if ($schemaParam . $tDump === $name || $tDump === $name) {
                    $tables[] = $table;
                }
            }
        }
        return $tables;
    }

    /**
     * Trie les tables pour que les tables référencées par une clé étrangère soient insérées en premier
     * En cas de dépendance circulaire, les tables restantes sont ajoutées dans leur ordre d'origine
     * @param Table[] $tables
     * @return Table[]
     */
    private function sortTablesByDependencies(array $tables): array
    {
        $dependencies = [];
        foreach ($tables as $table) {
            $name = $this->getTableKey($table);
            $dependencies[$name] = [];
            foreach ($table->getForeignKeys() as $foreignKey) {
                $referenced = strtolower($foreignKey->getReferencedTableName()->getUnqualifiedName()->getValue());
                if ($referenced !== $name) {
                    $dependencies[$name][$referenced] = true;
                }
            }
        }

        $sorted = [];
        $remaining = $tables;
        while ($remaining !== []) {
            $progress = false;
            foreach ($remaining as $key => $table) {
                $name = $this->getTableKey($table);
                $pending = array_filter(
                    array_keys($dependencies[$name]),
                    fn(string $dep) => isset($dependencies[$dep]) && !isset($sorted[$dep]),
                );
                if ($pending === []) {
                    $sorted[$name] = $table;
                    unset($remaining[$key]);
                    $progress = true;
                }
            }
            if (!$progress) {
                foreach ($remaining as $table) {
                    $sorted[$this->getTableKey($table)] = $table;
                }
                break;
            }
        }

        return array_values($sorted);
    }

    /**
     * Retourne le nom non qualifié d'une table en minuscule
     * @param Table $table
     * @return string
     */
    private function getTableKey(Table $table): string
    {
        return strtolower($table->getObjectName()->getUnqualifiedName()->getValue());
    }

    /**
     * Écrit les requêtes INSERT d'une table par lot de INSERT_BATCH_SIZE lignes
     * @param resource $handle
     * @param Connection $connection
     * @param Table $table
     * @return void
     * @throws Exception
     */
    private function writeInsertQueries($handle, Connection $connection, Table $table): void
    {
        $platform = $connection->getDatabasePlatform();
        $tableName = $table->getObjectName()->toSQL($platform);

        foreach ($this->fetchRowsByBatch($connection, $table) as $rows) {
            $columns = array_map(
                static fn(string $column) => $platform->quoteSingleIdentifier($column),
                array_keys($rows[0]),
            );
            $values = array_map(
                fn(array $row) => '(' .
                    implode(', ', array_map(fn($value) => $this->formatValue($connection, $value), $row)) .
                    ')',
                $rows,
            );

            $this->write(
                $handle,
                'INSERT INTO ' .
                    $tableName .
                    ' (' .
                    implode(', ', $columns) .
                    ') VALUES ' .
                    implode(', ', $values) .
                    ';',
            );
        }
    }

    /**
     * Retourne les lignes d'une table par lot de INSERT_BATCH_SIZE lignes
     * Avec une clé primaire, les lignes sont lues page par page pour ne pas charger toute la table en mémoire
     * @param Connection $connection
     * @param Table $table
     * @return \Generator<list<array<string, mixed>>>
     * @throws Exception
     */
    private function fetchRowsByBatch(Connection $connection, Table $table): \Generator
    {
        $platform = $connection->getDatabasePlatform();
        $query = 'SELECT * FROM ' . $table->getObjectName()->toSQL($platform);

        $primaryKey = $table->getPrimaryKeyConstraint();
        if ($primaryKey === null) {
            // Sans ordre stable, une pagination pourrait sauter ou dupliquer des lignes
            $rows = [];
            foreach ($connection->executeQuery($query)->iterateAssociative() as $row) {
                $rows[] = $row;
                if (count($rows) >= self::INSERT_BATCH_SIZE) {
                    yield $rows;
                    $rows = [];
                }
            }
            if ($rows !== []) {
                yield $rows;
            }
            return;
        }

        // Tri par clé primaire pour insérer les parents avant les enfants sur les tables auto-référencées
        $query .=
            ' ORDER BY ' .
            implode(
                ', ',
                array_map(
                    static fn(UnqualifiedName $column) => $column->toSQL($platform),
                    $primaryKey->getColumnNames(),
                ),
            );

        $offset = 0;
        do {
            $rows = $connection
                ->executeQuery($platform->modifyLimitQuery($query, self::INSERT_BATCH_SIZE, $offset))
                ->fetchAllAssociative();
            if ($rows !== []) {
                yield $rows;
            }
            $offset += self::INSERT_BATCH_SIZE;
        } while (count($rows) === self::INSERT_BATCH_SIZE);
    }

    /**
     * Formate une valeur pour une requête INSERT
     * @param Connection $connection
     * @param mixed $value
     * @return string
     */
    private function formatValue(Connection $connection, mixed $value): string
    {
        if (is_resource($value)) {
            $value = stream_get_contents($value);
        }

        return match (true) {
            $value === null => 'NULL',
            is_bool($value) => $value ? 'true' : 'false',
            is_int($value), is_float($value) => (string) $value,
            default => $connection->quote((string) $value),
        };
    }

    /**
     * Écrit une ligne dans le fichier de dump
     * @param resource $handle
     * @param string $line
     * @return void
     */
    private function write($handle, string $line): void
    {
        if (fwrite($handle, $line . "\n") === false) {
            throw new IOException("Impossible d'écrire dans le fichier de dump");
        }
    }
}
