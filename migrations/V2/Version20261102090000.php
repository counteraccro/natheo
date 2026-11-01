<?php

declare(strict_types=1);
/**
 * Migration : libellé de tag unique par locale
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace DoctrineMigrations\V2;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Les doublons existants (locale, label) sont renommés avec leur id avant la pose de la contrainte
 */
final class Version20261102090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Contrainte unique (locale, label) sur tag_translation';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            "UPDATE tag_translation t
            INNER JOIN (
                SELECT MIN(id) AS keep_id, locale, label FROM tag_translation GROUP BY locale, label HAVING COUNT(*) > 1
            ) d ON t.locale = d.locale AND t.label = d.label AND t.id <> d.keep_id
            SET t.label = CONCAT(LEFT(t.label, 240), ' (', t.id, ')')",
        );
        $this->addSql('CREATE UNIQUE INDEX UNIQ_TAG_TRANSLATION_LOCALE_LABEL ON tag_translation (locale, label)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_TAG_TRANSLATION_LOCALE_LABEL ON tag_translation');
    }
}
