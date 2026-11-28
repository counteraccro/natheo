<?php

declare(strict_types=1);
/**
 * Migration v2.1 (MySQL / MariaDB) : nouveau modèle de la médiathèque
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace DoctrineMigrations\V2_1\Mysql;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Équivalent MySQL / MariaDB de DoctrineMigrations\V2_1\Postgresql\Version20261128102400
 */
final class Version20261128102400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Médiathèque : traductions, stockage à plat, corbeille datée, propriétaire des dossiers (MySQL / MariaDB)';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(!($this->platform instanceof AbstractMySQLPlatform), 'Migration réservée à MySQL / MariaDB');

        // Médiathèque d'une 2.0 non reprise : fichiers rangés autrement, lignes supprimées
        $this->addSql('DELETE FROM media');
        $this->addSql('DELETE FROM media_folder');
        $this->addSql("DELETE FROM option_system WHERE `key` = 'OS_MEDIA_CREATE_PHYSICAL_FOLDER'");
        $this->addSql(
            'CREATE TABLE media_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(10) NOT NULL, title VARCHAR(255) DEFAULT NULL, alt VARCHAR(255) DEFAULT NULL, media_id INT NOT NULL, UNIQUE INDEX UNIQ_MEDIA_TRANSLATION_MEDIA_LOCALE (media_id, locale), INDEX IDX_430137FCEA9FDD75 (media_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'ALTER TABLE media_translation ADD CONSTRAINT FK_430137FCEA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE CASCADE',
        );
        $this->addSql('ALTER TABLE media DROP FOREIGN KEY `FK_6A2CA10CA76ED395`');
        $this->addSql(
            'ALTER TABLE media ADD mime_type VARCHAR(100) NOT NULL, ADD width INT DEFAULT NULL, ADD height INT DEFAULT NULL, ADD duration INT DEFAULT NULL, ADD hash VARCHAR(64) NOT NULL, ADD has_thumbnail TINYINT NOT NULL, ADD trashed_at DATETIME DEFAULT NULL, ADD trash_root TINYINT NOT NULL, DROP title, DROP description, DROP thumbnail, DROP web_path, DROP disabled, DROP trash, CHANGE name name VARCHAR(255) NOT NULL, CHANGE type type VARCHAR(20) NOT NULL, CHANGE extension extension VARCHAR(20) NOT NULL, CHANGE path path VARCHAR(255) NOT NULL, CHANGE update_at update_at DATETIME NOT NULL, CHANGE user_id user_id INT DEFAULT NULL',
        );
        $this->addSql(
            'ALTER TABLE media ADD CONSTRAINT FK_6A2CA10CA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE SET NULL',
        );
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6A2CA10CB548B0F ON media (path)');
        $this->addSql('CREATE INDEX IDX_MEDIA_HASH ON media (hash)');
        $this->addSql('ALTER TABLE media_folder DROP FOREIGN KEY `FK_50DB9313727ACA70`');
        $this->addSql(
            'ALTER TABLE media_folder ADD tree_path VARCHAR(255) NOT NULL, ADD trashed_at DATETIME DEFAULT NULL, ADD trash_root TINYINT NOT NULL, ADD user_id INT DEFAULT NULL, DROP path, DROP disabled, DROP trash, CHANGE update_at update_at DATETIME NOT NULL',
        );
        $this->addSql(
            'ALTER TABLE media_folder ADD CONSTRAINT FK_50DB9313727ACA70 FOREIGN KEY (parent_id) REFERENCES media_folder (id)',
        );
        $this->addSql(
            'ALTER TABLE media_folder ADD CONSTRAINT FK_50DB9313A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE SET NULL',
        );
        $this->addSql('CREATE INDEX IDX_MEDIA_FOLDER_TREE_PATH ON media_folder (tree_path)');
        $this->addSql('CREATE INDEX IDX_50DB9313A76ED395 ON media_folder (user_id)');
    }

    public function down(Schema $schema): void
    {
        $this->skipIf(!($this->platform instanceof AbstractMySQLPlatform), 'Migration réservée à MySQL / MariaDB');

        $this->addSql('DELETE FROM media_translation');
        $this->addSql('DELETE FROM media');
        $this->addSql('DELETE FROM media_folder');
        $this->addSql('ALTER TABLE media_translation DROP FOREIGN KEY FK_430137FCEA9FDD75');
        $this->addSql('DROP TABLE media_translation');
        $this->addSql('ALTER TABLE media DROP FOREIGN KEY FK_6A2CA10CA76ED395');
        $this->addSql('DROP INDEX UNIQ_6A2CA10CB548B0F ON media');
        $this->addSql('DROP INDEX IDX_MEDIA_HASH ON media');
        $this->addSql(
            'ALTER TABLE media ADD title VARCHAR(255) NOT NULL, ADD description LONGTEXT DEFAULT NULL, ADD thumbnail VARCHAR(255) DEFAULT NULL, ADD web_path LONGTEXT NOT NULL, ADD disabled TINYINT NOT NULL, ADD trash TINYINT NOT NULL, DROP mime_type, DROP width, DROP height, DROP duration, DROP hash, DROP has_thumbnail, DROP trashed_at, DROP trash_root, CHANGE name name LONGTEXT NOT NULL, CHANGE type type VARCHAR(50) NOT NULL, CHANGE extension extension VARCHAR(50) NOT NULL, CHANGE path path LONGTEXT NOT NULL, CHANGE update_at update_at DATETIME DEFAULT NULL, CHANGE user_id user_id INT NOT NULL',
        );
        $this->addSql(
            'ALTER TABLE media ADD CONSTRAINT `FK_6A2CA10CA76ED395` FOREIGN KEY (user_id) REFERENCES `user` (id) ON UPDATE NO ACTION ON DELETE NO ACTION',
        );
        $this->addSql('ALTER TABLE media_folder DROP FOREIGN KEY FK_50DB9313727ACA70');
        $this->addSql('ALTER TABLE media_folder DROP FOREIGN KEY FK_50DB9313A76ED395');
        $this->addSql('DROP INDEX IDX_MEDIA_FOLDER_TREE_PATH ON media_folder');
        $this->addSql('DROP INDEX IDX_50DB9313A76ED395 ON media_folder');
        $this->addSql(
            'ALTER TABLE media_folder ADD path LONGTEXT NOT NULL, ADD trash TINYINT NOT NULL, DROP tree_path, DROP trashed_at, DROP user_id, ADD disabled TINYINT NOT NULL, DROP trash_root, CHANGE update_at update_at DATETIME DEFAULT NULL',
        );
        $this->addSql(
            'ALTER TABLE media_folder ADD CONSTRAINT `FK_50DB9313727ACA70` FOREIGN KEY (parent_id) REFERENCES media_folder (id) ON UPDATE NO ACTION ON DELETE CASCADE',
        );
    }
}
