<?php

declare(strict_types=1);
/**
 * Migration v2.1 (MySQL / MariaDB) : table des brouillons de pages publiées
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace DoctrineMigrations\V2_1\Mysql;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Équivalent MySQL / MariaDB de DoctrineMigrations\V2_1\Postgresql\Version20261108154731
 */
final class Version20261108154731 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Table page_draft : brouillons des pages publiées (MySQL / MariaDB)';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(!($this->platform instanceof AbstractMySQLPlatform), 'Migration réservée à MySQL / MariaDB');

        $this->addSql(
            'CREATE TABLE page_draft (id INT AUTO_INCREMENT NOT NULL, data JSON NOT NULL, created_at DATETIME NOT NULL, update_at DATETIME NOT NULL, page_id INT NOT NULL, user_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_20FD6942C4663E4 (page_id), INDEX IDX_20FD6942A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'ALTER TABLE page_draft ADD CONSTRAINT FK_20FD6942C4663E4 FOREIGN KEY (page_id) REFERENCES page (id) ON DELETE CASCADE',
        );
        $this->addSql(
            'ALTER TABLE page_draft ADD CONSTRAINT FK_20FD6942A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE SET NULL',
        );
    }

    public function down(Schema $schema): void
    {
        $this->skipIf(!($this->platform instanceof AbstractMySQLPlatform), 'Migration réservée à MySQL / MariaDB');

        $this->addSql('ALTER TABLE page_draft DROP FOREIGN KEY FK_20FD6942C4663E4');
        $this->addSql('ALTER TABLE page_draft DROP FOREIGN KEY FK_20FD6942A76ED395');
        $this->addSql('DROP TABLE page_draft');
    }
}
