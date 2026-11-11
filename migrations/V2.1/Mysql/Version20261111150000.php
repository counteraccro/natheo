<?php

declare(strict_types=1);
/**
 * Migration v2.1 (MySQL / MariaDB) : sessions API des utilisateurs
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace DoctrineMigrations\V2_1\Mysql;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Équivalent MySQL / MariaDB de DoctrineMigrations\V2_1\Postgresql\Version20261111150000
 */
final class Version20261111150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Table user_session, suppression des anciens tokens utilisateur, durée du refresh token (MySQL / MariaDB)';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(!($this->platform instanceof AbstractMySQLPlatform), 'Migration réservée à MySQL / MariaDB');

        $this->addSql(
            'CREATE TABLE user_session (id INT AUTO_INCREMENT NOT NULL, access_token_hash VARCHAR(64) NOT NULL, access_token_expires_at DATETIME NOT NULL, refresh_token_hash VARCHAR(64) NOT NULL, refresh_token_expires_at DATETIME NOT NULL, previous_refresh_token_hash VARCHAR(64) DEFAULT NULL, user_agent VARCHAR(255) DEFAULT NULL, ip VARCHAR(45) DEFAULT NULL, created_at DATETIME NOT NULL, last_used_at DATETIME DEFAULT NULL, user_id INT NOT NULL, api_token_id INT NOT NULL, UNIQUE INDEX UNIQ_8849CBDE9982CF5B (access_token_hash), UNIQUE INDEX UNIQ_8849CBDE7288F9EC (refresh_token_hash), INDEX IDX_8849CBDEA76ED395 (user_id), INDEX IDX_8849CBDE92E52D36 (api_token_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'ALTER TABLE user_session ADD CONSTRAINT FK_8849CBDEA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE',
        );
        $this->addSql(
            'ALTER TABLE user_session ADD CONSTRAINT FK_8849CBDE92E52D36 FOREIGN KEY (api_token_id) REFERENCES api_token (id) ON DELETE CASCADE',
        );

        // Anciens tokens utilisateur, remplacés par les sessions
        $this->addSql("DELETE FROM user_data WHERE `key` IN ('KEY_TOKEN_CONNEXION', 'TIME_VALIDATE_TOKEN')");
        // Les anciennes durées (30 min à 1 jour) deviennent 1 jour, la plus courte des nouvelles
        $this->addSql("UPDATE option_system SET value = '1440' WHERE `key` = 'OS_API_TIME_VALIDATE_USER_TOKEN'");
    }

    public function down(Schema $schema): void
    {
        $this->skipIf(!($this->platform instanceof AbstractMySQLPlatform), 'Migration réservée à MySQL / MariaDB');

        $this->addSql('ALTER TABLE user_session DROP FOREIGN KEY FK_8849CBDEA76ED395');
        $this->addSql('ALTER TABLE user_session DROP FOREIGN KEY FK_8849CBDE92E52D36');
        $this->addSql('DROP TABLE user_session');
    }
}
