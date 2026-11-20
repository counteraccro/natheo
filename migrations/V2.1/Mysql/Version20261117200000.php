<?php

declare(strict_types=1);
/**
 * Migration v2.1 (MySQL / MariaDB) : journal des actions du back-office
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace DoctrineMigrations\V2_1\Mysql;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Équivalent MySQL / MariaDB de DoctrineMigrations\V2_1\Postgresql\Version20261117200000
 */
final class Version20261117200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Table action_log : journal des actions du back-office (MySQL / MariaDB)';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(!($this->platform instanceof AbstractMySQLPlatform), 'Migration réservée à MySQL / MariaDB');

        $this->addSql(
            'CREATE TABLE action_log (id INT AUTO_INCREMENT NOT NULL, module VARCHAR(50) NOT NULL, action VARCHAR(50) NOT NULL, resource VARCHAR(100) NOT NULL, resource_id VARCHAR(50) DEFAULT NULL, label VARCHAR(255) DEFAULT NULL, ip VARCHAR(45) DEFAULT NULL, created_at DATETIME NOT NULL, user_id INT DEFAULT NULL, impersonator_id INT DEFAULT NULL, INDEX IDX_ACTION_LOG_CREATED_AT (created_at), INDEX IDX_ACTION_LOG_RESOURCE (resource, resource_id), INDEX IDX_B2C5F685A76ED395 (user_id), INDEX IDX_B2C5F685D1107CFF (impersonator_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'ALTER TABLE action_log ADD CONSTRAINT FK_B2C5F685A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE SET NULL',
        );
        $this->addSql(
            'ALTER TABLE action_log ADD CONSTRAINT FK_B2C5F685D1107CFF FOREIGN KEY (impersonator_id) REFERENCES `user` (id) ON DELETE SET NULL',
        );
    }

    public function down(Schema $schema): void
    {
        $this->skipIf(!($this->platform instanceof AbstractMySQLPlatform), 'Migration réservée à MySQL / MariaDB');

        $this->addSql('ALTER TABLE action_log DROP FOREIGN KEY FK_B2C5F685A76ED395');
        $this->addSql('ALTER TABLE action_log DROP FOREIGN KEY FK_B2C5F685D1107CFF');
        $this->addSql('DROP TABLE action_log');
    }
}
