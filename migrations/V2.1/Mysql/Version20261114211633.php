<?php

declare(strict_types=1);
/**
 * Migration v2.1 (MySQL / MariaDB) : texte du pied de page par langue
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace DoctrineMigrations\V2_1\Mysql;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Équivalent MySQL / MariaDB de DoctrineMigrations\V2_1\Postgresql\Version20261114211633
 */
final class Version20261114211633 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'OS_FRONT_FOOTER_TEXTE traduisible : valeur actuelle placée sous la langue par défaut (MySQL / MariaDB)';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(!($this->platform instanceof AbstractMySQLPlatform), 'Migration réservée à MySQL / MariaDB');

        $value = $this->getOption('OS_FRONT_FOOTER_TEXTE');
        if ($value === null || $value === '' || $this->isTranslations($value)) {
            return;
        }
        $this->updateFooter(
            json_encode([$this->getDefaultLanguage() => $value], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        );
    }

    public function down(Schema $schema): void
    {
        $this->skipIf(!($this->platform instanceof AbstractMySQLPlatform), 'Migration réservée à MySQL / MariaDB');

        $value = $this->getOption('OS_FRONT_FOOTER_TEXTE');
        if ($value === null || !$this->isTranslations($value)) {
            return;
        }
        $this->updateFooter((string) (json_decode($value, true)[$this->getDefaultLanguage()] ?? ''));
    }

    /**
     * Valeur d'une option système, null si elle n'est pas en base
     * @param string $key
     * @return string|null
     */
    private function getOption(string $key): ?string
    {
        $value = $this->connection->fetchOne('SELECT value FROM option_system WHERE `key` = ?', [$key]);
        return is_string($value) ? $value : null;
    }

    /**
     * Langue par défaut du site
     * @return string
     */
    private function getDefaultLanguage(): string
    {
        return $this->getOption('OS_DEFAULT_LANGUAGE') ?: 'fr';
    }

    /**
     * Indique si la valeur est déjà un objet JSON de valeurs par langue
     * @param string $value
     * @return bool
     */
    private function isTranslations(string $value): bool
    {
        $decoded = json_decode($value, true);
        return is_array($decoded) && $decoded !== [] && !array_is_list($decoded);
    }

    /**
     * Remplace la valeur du texte du pied de page
     * @param string $value
     * @return void
     */
    private function updateFooter(string $value): void
    {
        $this->addSql('UPDATE option_system SET value = ? WHERE `key` = ?', [$value, 'OS_FRONT_FOOTER_TEXTE']);
    }
}
