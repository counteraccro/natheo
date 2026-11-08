<?php

declare(strict_types=1);
/**
 * Migration v2.1 (MySQL / MariaDB) : état de validation des pages
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace DoctrineMigrations\V2_1\Mysql;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Équivalent MySQL / MariaDB de DoctrineMigrations\V2_1\Postgresql\Version20261108204305
 */
final class Version20261108204305 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Table page : état, auteur, date et motif de refus de la validation (MySQL / MariaDB)';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(!($this->platform instanceof AbstractMySQLPlatform), 'Migration réservée à MySQL / MariaDB');

        $this->addSql(
            'ALTER TABLE page ADD validation_status SMALLINT DEFAULT 0 NOT NULL, ADD validation_at DATETIME DEFAULT NULL, ADD validation_comment LONGTEXT DEFAULT NULL, ADD validation_user_id INT DEFAULT NULL',
        );
        $this->addSql(
            'ALTER TABLE page ADD CONSTRAINT FK_140AB620B6EB8E9B FOREIGN KEY (validation_user_id) REFERENCES `user` (id) ON DELETE SET NULL',
        );
        $this->addSql('CREATE INDEX IDX_140AB620B6EB8E9B ON page (validation_user_id)');
    }

    public function down(Schema $schema): void
    {
        $this->skipIf(!($this->platform instanceof AbstractMySQLPlatform), 'Migration réservée à MySQL / MariaDB');

        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB620B6EB8E9B');
        $this->addSql('DROP INDEX IDX_140AB620B6EB8E9B ON page');
        $this->addSql(
            'ALTER TABLE page DROP validation_status, DROP validation_at, DROP validation_comment, DROP validation_user_id',
        );
    }
}
