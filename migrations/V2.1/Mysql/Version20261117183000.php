<?php

declare(strict_types=1);
/**
 * Migration v2.1 (MySQL / MariaDB) : type des tokens d'API et token interne du back-office
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace DoctrineMigrations\V2_1\Mysql;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Équivalent MySQL / MariaDB de DoctrineMigrations\V2_1\Postgresql\Version20261117183000
 */
final class Version20261117183000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Colonnes scope et internal de api_token : les tokens existants deviennent publics (MySQL / MariaDB)';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(!($this->platform instanceof AbstractMySQLPlatform), 'Migration réservée à MySQL / MariaDB');

        $this->addSql("ALTER TABLE api_token ADD scope VARCHAR(20) DEFAULT 'public' NOT NULL, ADD internal TINYINT DEFAULT 0 NOT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->skipIf(!($this->platform instanceof AbstractMySQLPlatform), 'Migration réservée à MySQL / MariaDB');

        $this->addSql('ALTER TABLE api_token DROP scope, DROP internal');
    }
}
