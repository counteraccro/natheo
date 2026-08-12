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

    /**
     * Retourne la stratégie courante
     * @return string
     */
    static function current(): string
    {
        return self::MYSQL->value;
    }
}
