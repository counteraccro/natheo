<?php

declare(strict_types=1);
/**
 * Migration v2.0 (MySQL / MariaDB) : tokens hachés, propriétaire des tags, unicité des libellés de tag et des URL de page
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace DoctrineMigrations\V2\Mysql;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Regroupe les évolutions du schéma postérieures à la migration initiale :
 * - tokens API / utilisateur hachés en SHA-256, expiration et dernière utilisation des tokens API ;
 * - propriétaire des tags (les tags existants restent sans propriétaire, gérés par l'admin seul) ;
 * - libellé de tag unique par locale, couleurs #rgb converties en #rrggbb ;
 * - URL de page unique par locale.
 * Les doublons existants sont renommés avant la pose des contraintes uniques.
 */
final class Version20261103090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Tokens hachés, propriétaire des tags, unicité (locale, label) des tags et (locale, url) des pages (MySQL / MariaDB)';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(!$this->platform instanceof AbstractMySQLPlatform, 'Migration réservée à MySQL / MariaDB');

        // Hash avant de réduire la colonne à 64 caractères, sinon les tokens existants seraient tronqués
        $this->addSql('UPDATE api_token SET token = SHA2(token, 256)');
        $this->addSql("UPDATE user_data SET value = SHA2(value, 256) WHERE `key` = 'KEY_TOKEN_CONNEXION'");
        $this->addSql(
            "UPDATE option_system SET value = '1440' WHERE `key` = 'OS_API_TIME_VALIDATE_USER_TOKEN' AND value = '-1'",
        );
        $this->addSql(
            'ALTER TABLE api_token ADD expires_at DATETIME DEFAULT NULL, ADD last_used_at DATETIME DEFAULT NULL, CHANGE token token VARCHAR(64) NOT NULL',
        );
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7BA2F5EB5F37A13B ON api_token (token)');

        $this->addSql('ALTER TABLE tag ADD user_id INT DEFAULT NULL');
        $this->addSql(
            'ALTER TABLE tag ADD CONSTRAINT FK_389B783A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL',
        );
        $this->addSql('CREATE INDEX IDX_389B783A76ED395 ON tag (user_id)');

        $this->addSql(
            "UPDATE tag_translation t
            INNER JOIN (
                SELECT MIN(id) AS keep_id, locale, label FROM tag_translation GROUP BY locale, label HAVING COUNT(*) > 1
            ) d ON t.locale = d.locale AND t.label = d.label AND t.id <> d.keep_id
            SET t.label = CONCAT(LEFT(t.label, 240), ' (', t.id, ')')",
        );
        $this->addSql(
            "UPDATE tag SET color = CONCAT('#',
                REPEAT(SUBSTRING(color, 2, 1), 2), REPEAT(SUBSTRING(color, 3, 1), 2), REPEAT(SUBSTRING(color, 4, 1), 2))
            WHERE color REGEXP '^#[0-9a-fA-F]{3}$'",
        );
        $this->addSql('CREATE UNIQUE INDEX UNIQ_TAG_TRANSLATION_LOCALE_LABEL ON tag_translation (locale, label)');

        // La page la plus ancienne garde l'URL, les autres reçoivent le suffixe « -<id de la page> »
        $this->addSql(
            "UPDATE page_translation t
            INNER JOIN (
                SELECT MIN(page_id) AS keep_page_id, locale, url FROM page_translation GROUP BY locale, url HAVING COUNT(*) > 1
            ) d ON t.locale = d.locale AND t.url = d.url AND t.page_id <> d.keep_page_id
            SET t.url = CONCAT(LEFT(t.url, 240), '-', t.page_id)",
        );
        $this->addSql('CREATE UNIQUE INDEX UNIQ_PAGE_TRANSLATION_LOCALE_URL ON page_translation (locale, url)');
    }

    public function down(Schema $schema): void
    {
        $this->skipIf(!$this->platform instanceof AbstractMySQLPlatform, 'Migration réservée à MySQL / MariaDB');

        $this->addSql('DROP INDEX UNIQ_PAGE_TRANSLATION_LOCALE_URL ON page_translation');
        $this->addSql('DROP INDEX UNIQ_TAG_TRANSLATION_LOCALE_LABEL ON tag_translation');

        $this->addSql('ALTER TABLE tag DROP FOREIGN KEY FK_389B783A76ED395');
        $this->addSql('DROP INDEX IDX_389B783A76ED395 ON tag');
        $this->addSql('ALTER TABLE tag DROP user_id');

        // Les hash ne sont pas réversibles : les tokens devront être régénérés après un retour arrière
        $this->addSql('DROP INDEX UNIQ_7BA2F5EB5F37A13B ON api_token');
        $this->addSql(
            'ALTER TABLE api_token DROP expires_at, DROP last_used_at, CHANGE token token VARCHAR(255) NOT NULL',
        );
    }
}
