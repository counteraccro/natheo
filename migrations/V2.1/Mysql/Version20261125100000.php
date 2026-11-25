<?php

declare(strict_types=1);
/**
 * Migration v2.1 (MySQL / MariaDB) : suppression de la table des statistiques de FAQ
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace DoctrineMigrations\V2_1\Mysql;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Équivalent MySQL / MariaDB de DoctrineMigrations\V2_1\Postgresql\Version20261125100000
 */
final class Version20261125100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Suppression de la table faq_statistique (MySQL / MariaDB)';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(!($this->platform instanceof AbstractMySQLPlatform), 'Migration réservée à MySQL / MariaDB');

        $this->addSql('ALTER TABLE faq_statistique DROP FOREIGN KEY FK_976F159481BEC8C2');
        $this->addSql('DROP TABLE faq_statistique');
    }

    public function down(Schema $schema): void
    {
        $this->skipIf(!($this->platform instanceof AbstractMySQLPlatform), 'Migration réservée à MySQL / MariaDB');

        $this->addSql(
            'CREATE TABLE faq_statistique (id INT AUTO_INCREMENT NOT NULL, `key` VARCHAR(255) NOT NULL, value VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, update_at DATETIME DEFAULT NULL, faq_id INT NOT NULL, INDEX IDX_976F159481BEC8C2 (faq_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'ALTER TABLE faq_statistique ADD CONSTRAINT FK_976F159481BEC8C2 FOREIGN KEY (faq_id) REFERENCES faq (id)',
        );
    }
}
