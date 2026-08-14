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
    const string CURRENT = self::MYSQL->value;

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
            'driver' => 'pdo_sqlite',
        ],
        self::MYSQL->value => [
            'driver' => 'pdo_mysql',
        ],
    ];

    /**
     * Retourne la stratégie courante
     * @return string
     */
    static function current(): string
    {
        return self::CURRENT;
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
