<?php

declare(strict_types=1);
/**
 * Migration v2.1 (PostgreSQL) : type des tokens d'API et colonne internal
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace DoctrineMigrations\V2_1\Postgresql;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Équivalent PostgreSQL de DoctrineMigrations\V2_1\Mysql\Version20261117183000
 */
final class Version20261117183000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Colonnes scope et internal de api_token : les tokens existants deviennent publics (PostgreSQL)';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(!($this->platform instanceof PostgreSQLPlatform), 'Migration réservée à PostgreSQL');

        $this->addSql("ALTER TABLE api_token ADD scope VARCHAR(20) DEFAULT 'public' NOT NULL");
        $this->addSql('ALTER TABLE api_token ADD internal BOOLEAN DEFAULT false NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->skipIf(!($this->platform instanceof PostgreSQLPlatform), 'Migration réservée à PostgreSQL');

        $this->addSql('ALTER TABLE api_token DROP scope');
        $this->addSql('ALTER TABLE api_token DROP internal');
    }
}
