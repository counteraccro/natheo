<?php

declare(strict_types=1);
/**
 *
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Installation;

enum DoctrineStrategy: string
{
    /**
     * Stratégie de génération des id des entités, IDENTITY convient à MySQL et à PostgreSQL (DBAL 4)
     * Constante car utilisée dans les attributs #[ORM\GeneratedValue], à ne pas confondre avec current()
     */
    const string CURRENT = self::MYSQL->value;

    /**
     * Schémas de DATABASE_URL correspondant à PostgreSQL
     */
    private const array POSTGRESQL_SCHEMES = ['postgres', 'postgresql', 'pgsql', 'pdo-pgsql'];

    /**
     * Stratégie Doctrine pour postgreSql
     * @var string
     */
    case POSTGRESQL = 'SEQUENCE';

    /**
     * Stratégie Doctrine pour Mysql
     * @var string
     */
    case MYSQL = 'IDENTITY';

    const CONF = [
        self::POSTGRESQL->value => [
            'driver' => 'pdo_pgsql',
        ],
        self::MYSQL->value => [
            'driver' => 'pdo_mysql',
        ],
    ];

    /**
     * Retourne la valeur du SGBD courant (MYSQL ou POSTGRESQL), déduit du schéma de DATABASE_URL
     * @return string
     */
    static function current(): string
    {
        $databaseUrl = $_SERVER['DATABASE_URL'] ?? ($_ENV['DATABASE_URL'] ?? (getenv('DATABASE_URL') ?: ''));
        return self::fromScheme((string) strstr((string) $databaseUrl, '://', true));
    }

    /**
     * Retourne la valeur du SGBD (MYSQL ou POSTGRESQL) correspondant au schéma d'une DATABASE_URL (mysql, postgresql...)
     * @param string $scheme
     * @return string
     */
    static function fromScheme(string $scheme): string
    {
        return in_array(strtolower($scheme), self::POSTGRESQL_SCHEMES, true)
            ? self::POSTGRESQL->value
            : self::MYSQL->value;
    }

    /**
     * Retourne le driver en fonction de la strategy
     * @param string $strategy
     * @return string
     */
    static function getDriver(string $strategy): string
    {
        if (!isset(self::CONF[$strategy])) {
            return '';
        }
        return self::CONF[$strategy]['driver'];
    }
}
