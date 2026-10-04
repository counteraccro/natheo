<?php

declare(strict_types=1);

namespace DoctrineMigrations\V2;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Sécurisation des tokens : hash SHA-256 des tokens API et utilisateur, expiration et dernière utilisation des tokens API
 */
final class Version20261003081354 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Hash des tokens API / utilisateur, ajout expires_at et last_used_at sur api_token';
    }

    public function up(Schema $schema): void
    {
        // Hash avant de réduire la colonne à 64 caractères, sinon les tokens existants seraient tronqués
        $this->addSql('UPDATE api_token SET token = SHA2(token, 256)');
        $this->addSql("UPDATE user_data SET value = SHA2(value, 256) WHERE `key` = 'KEY_TOKEN_CONNEXION'");
        $this->addSql(
            "UPDATE option_system SET value = '1440' WHERE `key` = 'OS_API_TIME_VALIDATE_USER_TOKEN' AND value = '-1'",
        );
        $this->addSql('ALTER TABLE api_token ADD expires_at DATETIME DEFAULT NULL, ADD last_used_at DATETIME DEFAULT NULL, CHANGE token token VARCHAR(64) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7BA2F5EB5F37A13B ON api_token (token)');
    }

    public function down(Schema $schema): void
    {
        // Les hash ne sont pas réversibles : les tokens devront être régénérés après un retour arrière
        $this->addSql('DROP INDEX UNIQ_7BA2F5EB5F37A13B ON api_token');
        $this->addSql('ALTER TABLE api_token DROP expires_at, DROP last_used_at, CHANGE token token VARCHAR(255) NOT NULL');
    }
}
