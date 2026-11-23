<?php

declare(strict_types=1);
/**
 * Migration v2.1 (MySQL / MariaDB) : table des modules et opérations d'API désactivés du back-office
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace DoctrineMigrations\V2_1\Mysql;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Équivalent MySQL / MariaDB de DoctrineMigrations\V2_1\Postgresql\Version20261123170000
 */
final class Version20261123170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table module_activation (MySQL / MariaDB)';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(!($this->platform instanceof AbstractMySQLPlatform), 'Migration réservée à MySQL / MariaDB');

        $this->addSql(
            'CREATE TABLE module_activation (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(20) NOT NULL, name VARCHAR(150) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_MODULE_ACTIVATION_TYPE_NAME (type, name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
    }

    public function down(Schema $schema): void
    {
        $this->skipIf(!($this->platform instanceof AbstractMySQLPlatform), 'Migration réservée à MySQL / MariaDB');

        $this->addSql('DROP TABLE module_activation');
    }
}
