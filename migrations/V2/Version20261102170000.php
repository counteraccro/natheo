<?php

declare(strict_types=1);
/**
 * Migration : URL de page unique par locale
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace DoctrineMigrations\V2;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Les doublons existants (locale, url) sont corrigés avant la pose de la contrainte :
 * la page la plus ancienne garde l'URL, les autres reçoivent le suffixe « -<id de la page> »
 */
final class Version20261102170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Contrainte unique (locale, url) sur page_translation, doublons suffixés par l\'id de la page';
    }

    public function up(Schema $schema): void
    {
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
        $this->addSql('DROP INDEX UNIQ_PAGE_TRANSLATION_LOCALE_URL ON page_translation');
    }
}
