<?php

declare(strict_types=1);
/**
 * Migration v2.0 (MySQL / MariaDB) : clés de traduction des emails et de la sidebar renommées
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace DoctrineMigrations\V2\Mysql;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Équivalent MySQL / MariaDB de DoctrineMigrations\V2\Postgresql\Version20261107120000
 */
final class Version20261107120000 extends AbstractMigration
{
    /**
     * Anciennes clés => nouvelles clés des libellés et descriptions de la sidebar
     */
    private const SIDEBAR_KEYS = [
        'global.dashboard' => 'sidebar.dashboard.label',
        'global.membre' => 'sidebar.member.label',
        'sidebar.membre.description' => 'sidebar.member.description',
        'global.content' => 'sidebar.content.label',
        'sidebar.global.content.description' => 'sidebar.content.description',
        'global.menu' => 'sidebar.menu.label',
        'global.page' => 'sidebar.page.label',
        'global.comment' => 'sidebar.comment.label',
        'global.tag' => 'sidebar.tag.label',
        'global.mediatheque' => 'sidebar.media.label',
        'sidebar.mediatheque.description' => 'sidebar.media.description',
        'global.faq' => 'sidebar.faq.label',
        'global.system' => 'sidebar.system.label',
        'sidebar.global.system.description' => 'sidebar.system.description',
        'global.user' => 'sidebar.user.label',
        'global.sidebar' => 'sidebar.sidebar.label',
        'global.system_options' => 'sidebar.option_system.label',
        'sidebar.system_options.description' => 'sidebar.option_system.description',
        'global.api_token' => 'sidebar.api_token.label',
        'global.mail' => 'sidebar.mail.label',
        'global.translate' => 'sidebar.translate.label',
        'global.logs' => 'sidebar.log.label',
        'global.update' => 'sidebar.update.label',
        'global.info' => 'sidebar.info.label',
        'global.devtools' => 'sidebar.tools.label',
        'sidebar.devtools.description' => 'sidebar.tools.description',
        'global.dev_options' => 'sidebar.advanced_options.label',
        'sidebar.dev_options.description' => 'sidebar.advanced_options.description',
        'global.sql' => 'sidebar.sql_manager.label',
        'sidebar.sql.description' => 'sidebar.sql_manager.description',
        'global.database' => 'sidebar.database_manager.label',
        'sidebar.database.description' => 'sidebar.database_manager.description',
        'global.html_element' => 'sidebar.demo_overwrite.label',
        'sidebar.html_element.description' => 'sidebar.demo_overwrite.description',
    ];

    /**
     * Anciennes clés => nouvelles clés des titres et descriptions des emails
     */
    private const MAIL_KEYS = [
        'mail.change.password.title' => 'mail.change_password.title',
        'mail.change.password.description' => 'mail.change_password.description',
        'mail.reset.password.title' => 'mail.reset_password.title',
        'mail.reset.password.description' => 'mail.reset_password.description',
        'mail.account.adm.disable.title' => 'mail.account_adm_disable.title',
        'mail.account.adm.disable.description' => 'mail.account_adm_disable.description',
        'mail.account.adm.enable.title' => 'mail.account_adm_enable.title',
        'mail.account.adm.enable.description' => 'mail.account_adm_enable.description',
        'mail.create.account.adm.title' => 'mail.create_account_adm.title',
        'mail.create.account.adm.description' => 'mail.create_account_adm.description',
    ];

    public function getDescription(): string
    {
        return 'Clés de traduction des emails et de la sidebar renommées (MySQL / MariaDB)';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(!$this->platform instanceof MySQLPlatform, 'Migration réservée à MySQL / MariaDB');
        $this->renameKeys(self::SIDEBAR_KEYS, self::MAIL_KEYS);
    }

    public function down(Schema $schema): void
    {
        $this->skipIf(!$this->platform instanceof MySQLPlatform, 'Migration réservée à MySQL / MariaDB');
        $this->renameKeys(array_flip(self::SIDEBAR_KEYS), array_flip(self::MAIL_KEYS));
    }

    /**
     * Remplace les clés stockées en base
     * @param array<string, string> $sidebarKeys
     * @param array<string, string> $mailKeys
     * @return void
     */
    private function renameKeys(array $sidebarKeys, array $mailKeys): void
    {
        foreach ($sidebarKeys as $old => $new) {
            $this->addSql('UPDATE sidebar_element SET label = ? WHERE label = ?', [$new, $old]);
            $this->addSql('UPDATE sidebar_element SET description = ? WHERE description = ?', [$new, $old]);
        }
        foreach ($mailKeys as $old => $new) {
            $this->addSql('UPDATE mail SET title = ? WHERE title = ?', [$new, $old]);
            $this->addSql('UPDATE mail SET description = ? WHERE description = ?', [$new, $old]);
        }
    }
}
