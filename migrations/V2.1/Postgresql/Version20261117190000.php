<?php

declare(strict_types=1);
/**
 * Migration v2.1 (PostgreSQL) : utilisateur d'origine d'une session API ouverte pendant une usurpation
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace DoctrineMigrations\V2_1\Postgresql;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Équivalent PostgreSQL de DoctrineMigrations\V2_1\Mysql\Version20261117190000
 */
final class Version20261117190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Colonne impersonator_id de user_session (PostgreSQL)';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(!($this->platform instanceof PostgreSQLPlatform), 'Migration réservée à PostgreSQL');

        $this->addSql('ALTER TABLE user_session ADD impersonator_id INT DEFAULT NULL');
        $this->addSql(
            'ALTER TABLE user_session ADD CONSTRAINT FK_8849CBDED1107CFF FOREIGN KEY (impersonator_id) REFERENCES "user" (id) ON DELETE SET NULL',
        );
        $this->addSql('CREATE INDEX IDX_8849CBDED1107CFF ON user_session (impersonator_id)');
    }

    public function down(Schema $schema): void
    {
        $this->skipIf(!($this->platform instanceof PostgreSQLPlatform), 'Migration réservée à PostgreSQL');

        $this->addSql('ALTER TABLE user_session DROP CONSTRAINT FK_8849CBDED1107CFF');
        $this->addSql('DROP INDEX IDX_8849CBDED1107CFF');
        $this->addSql('ALTER TABLE user_session DROP impersonator_id');
    }
}
