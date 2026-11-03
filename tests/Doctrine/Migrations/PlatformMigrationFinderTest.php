<?php

declare(strict_types=1);
/**
 * Test du finder de migrations filtré par SGBD
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Tests\Doctrine\Migrations;

use App\Doctrine\Migrations\PlatformMigrationFinder;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PlatformMigrationFinderTest extends KernelTestCase
{
    /**
     * Seules les migrations du SGBD courant sont retournées
     * @return void
     */
    public function testFindMigrationsByPlatform(): void
    {
        self::bootKernel();
        /** @var Connection $connection */
        $connection = self::getContainer()->get('doctrine')->getConnection();
        $finder = new PlatformMigrationFinder($connection);
        $dir = self::getContainer()->getParameter('kernel.project_dir') . '/migrations/V2/';

        $mysql = $finder->findMigrations($dir . 'Mysql', 'DoctrineMigrations\V2\Mysql');
        $postgresql = $finder->findMigrations($dir . 'Postgresql', 'DoctrineMigrations\V2\Postgresql');

        $platform = $connection->getDatabasePlatform();
        if ($platform instanceof AbstractMySQLPlatform) {
            $this->assertNotEmpty($mysql);
            $this->assertEmpty($postgresql);
        } elseif ($platform instanceof PostgreSQLPlatform) {
            $this->assertEmpty($mysql);
            $this->assertNotEmpty($postgresql);
        }

        // Chaque version existe pour les deux SGBD
        $versions = fn(string $sub) => array_map('basename', glob($dir . $sub . '/Version*.php'));
        $this->assertSame($versions('Mysql'), $versions('Postgresql'));
    }
}
