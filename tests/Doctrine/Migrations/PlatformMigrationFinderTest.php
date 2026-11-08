<?php

declare(strict_types=1);
/**
 * Test du finder de migrations filtré par SGBD
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Tests\Doctrine\Migrations;

use App\Doctrine\Migrations\PlatformMigrationFinder;
use App\Doctrine\Migrations\VersionNumberComparator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\Migrations\Version\Version;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PlatformMigrationFinderTest extends KernelTestCase
{
    /**
     * Dossiers de migrations => segment de namespace correspondant
     */
    private const array VERSION_FOLDERS = ['V2' => 'V2', 'V2.1' => 'V2_1'];

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
        $projectDir = self::getContainer()->getParameter('kernel.project_dir');

        foreach (self::VERSION_FOLDERS as $folder => $namespace) {
            $dir = $projectDir . '/migrations/' . $folder . '/';
            $mysql = $finder->findMigrations($dir . 'Mysql', 'DoctrineMigrations\\' . $namespace . '\\Mysql');
            $postgresql = $finder->findMigrations(
                $dir . 'Postgresql',
                'DoctrineMigrations\\' . $namespace . '\\Postgresql',
            );

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

    /**
     * Les migrations sont triées sur leur numéro de version, quel que soit le dossier de version
     * @return void
     */
    public function testVersionNumberComparator(): void
    {
        $comparator = new VersionNumberComparator();
        $v2 = new Version('DoctrineMigrations\\V2\\Mysql\\Version20261107120000');
        $v21 = new Version('DoctrineMigrations\\V2_1\\Mysql\\Version20261108154731');
        $v210 = new Version('DoctrineMigrations\\V2_10\\Mysql\\Version20300101000000');

        $this->assertLessThan(0, $comparator->compare($v2, $v21));
        $this->assertLessThan(0, $comparator->compare($v21, $v210));
        $this->assertGreaterThan(0, $comparator->compare($v210, $v2));
        $this->assertSame(0, $comparator->compare($v21, $v21));
    }
}
