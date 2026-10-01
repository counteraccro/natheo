<?php

declare(strict_types=1);
/**
 * Manager qui permet d'executer des RawQuery en fonction du SGBD
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Global\Database;

use App\Enum\Installation\DoctrineStrategy;
use App\Utils\Installation\InstallationConst;
use App\Utils\Tools\DatabaseManager\Query\RawMysqlQuery;
use App\Utils\Tools\DatabaseManager\Query\RawPostgresQuery;

class RawQueryManager
{
    /**
     * @var mixed|string
     */
    private mixed $rawClass;

    public function __construct()
    {
        $this->rawClass = match (DoctrineStrategy::current()) {
            DoctrineStrategy::MYSQL->value => RawMysqlQuery::class,
            DoctrineStrategy::POSTGRESQL->value => RawPostgresQuery::class,
        };
    }

    /**
     * Requête SQL pour obtenir l'ensemble des tables de la base de données
     * Paramètre attendu à l'exécution : schema
     * @return string
     */
    public function getQueryAllInformationSchema(): string
    {
        return $this->rawClass::getQueryAllInformationSchema();
    }

    /**
     * Retourne la structure d'une table
     * @param string $table
     * @param string $schema
     * @return string
     */
    public function getQueryStructureTable(string $table, string $schema = ''): string
    {
        return $this->rawClass::getQueryStructureTable($table, $schema);
    }

    /**
     * Vérifie si une table existe dans la base de données
     * Paramètres attendus à l'exécution : table, et schema si $withSchema vaut true
     * @param bool $withSchema
     * @return string
     */
    public function getQueryExistTable(bool $withSchema = true): string
    {
        return $this->rawClass::getQueryExistTable($withSchema);
    }

    /**
     * Permet d'obtenir la liste des bases de données
     * @return string
     */
    public function getQueryAllDatabase(): string
    {
        return $this->rawClass::getQueryAllDatabase();
    }

    /**
     * Permet pour purger les notifications
     * @param string $table nom de la table notification, déjà quoté
     * @return string
     */
    public function getQueryPurgeNotification(string $table): string
    {
        return $this->rawClass::getQueryPurgeNotification($table);
    }

    /**
     * Test la connexion au SGBD
     * @return string
     */
    public function getQueryCheckConnexion(): string
    {
        return $this->rawClass::getQueryCheckConnexion();
    }

    /**
     * Calcul un nombre de valeur en fonction d'une clée
     * @return string
     */
    public function getQueryTotalStatByKey(): string
    {
        return $this->rawClass::getQueryTotalStatByKey();
    }

    /**
     * Retourne les ids de page triés par la valeur (numérique) d'une statistique, du plus grand au plus petit
     * @param int $limit
     * @return string
     */
    public function getQueryPageMostViewedByKey(int $limit): string
    {
        return $this->rawClass::getQueryPageMostViewedByKey($limit);
    }
}
