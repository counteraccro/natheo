<?php

declare(strict_types=1);
/**
 * Migration : ajout du propriétaire des tags
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace DoctrineMigrations\V2;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Propriétaire des tags : les tags existants restent sans propriétaire (gérés par l'admin seul)
 */
final class Version20261101164334 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de user_id sur tag';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tag ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tag ADD CONSTRAINT FK_389B783A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_389B783A76ED395 ON tag (user_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tag DROP FOREIGN KEY FK_389B783A76ED395');
        $this->addSql('DROP INDEX IDX_389B783A76ED395 ON tag');
        $this->addSql('ALTER TABLE tag DROP user_id');
    }
}
