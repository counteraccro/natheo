<?php

declare(strict_types=1);
/**
 * Finder de migrations Doctrine filtré par SGBD
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Doctrine\Migrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\Migrations\Finder\MigrationFinder;
use Doctrine\Migrations\Finder\RecursiveRegexFinder;

/**
 * Chaque version de migration existe une fois par SGBD (migrations/V2/Mysql, migrations/V2/Postgresql) :
 * seules celles de la plateforme courante sont chargées, les autres n'apparaissent ni en « New » ni en statut
 */
class PlatformMigrationFinder implements MigrationFinder
{
    /**
     * Suffixe de namespace des migrations => classe de plateforme DBAL attendue
     */
    private const array PLATFORMS = [
        '\\Mysql' => AbstractMySQLPlatform::class,
        '\\Postgresql' => PostgreSQLPlatform::class,
    ];

    private MigrationFinder $finder;

    public function __construct(private readonly Connection $connection)
    {
        $this->finder = new RecursiveRegexFinder();
    }

    /**
     * @param string $directory
     * @param string|null $namespace
     * @return string[]
     * @throws Exception
     */
    public function findMigrations(string $directory, ?string $namespace = null): array
    {
        foreach (self::PLATFORMS as $suffix => $platformClass) {
            if ($namespace !== null && str_ends_with($namespace, $suffix)) {
                if (!$this->connection->getDatabasePlatform() instanceof $platformClass) {
                    return [];
                }
                break;
            }
        }

        return $this->finder->findMigrations($directory, $namespace);
    }
}
