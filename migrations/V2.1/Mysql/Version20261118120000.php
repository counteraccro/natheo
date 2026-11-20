<?php

declare(strict_types=1);
/**
 * Migration v2.1 (MySQL / MariaDB) : suppression de la table de la sidebar, construite depuis les modules déclarés
 * dans le code
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace DoctrineMigrations\V2_1\Mysql;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Équivalent MySQL / MariaDB de DoctrineMigrations\V2_1\Postgresql\Version20261118120000
 */
final class Version20261118120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Suppression de la table sidebar_element (MySQL / MariaDB)';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(!($this->platform instanceof AbstractMySQLPlatform), 'Migration réservée à MySQL / MariaDB');

        $this->addSql('ALTER TABLE sidebar_element DROP FOREIGN KEY FK_2420A342727ACA70');
        $this->addSql('DROP TABLE sidebar_element');
    }

    public function down(Schema $schema): void
    {
        $this->skipIf(!($this->platform instanceof AbstractMySQLPlatform), 'Migration réservée à MySQL / MariaDB');

        $this->addSql(
            'CREATE TABLE sidebar_element (id INT AUTO_INCREMENT NOT NULL, icon LONGTEXT NOT NULL, label VARCHAR(255) NOT NULL, route VARCHAR(255) NOT NULL, disabled TINYINT NOT NULL, role VARCHAR(100) NOT NULL, description LONGTEXT NOT NULL, `lock` TINYINT NOT NULL, created_at DATETIME NOT NULL, update_at DATETIME DEFAULT NULL, parent_id INT DEFAULT NULL, INDEX IDX_2420A342727ACA70 (parent_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'ALTER TABLE sidebar_element ADD CONSTRAINT FK_2420A342727ACA70 FOREIGN KEY (parent_id) REFERENCES sidebar_element (id) ON DELETE CASCADE',
        );
    }
}
