<?php
/**
 * Manager qui permet d'executer des RawQuery en fonction du SGBD
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Global\Database;

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
        $this->rawClass = match (InstallationConst::STRATEGY) {
            InstallationConst::STRATEGY_MYSQL => RawMysqlQuery::class,
            InstallationConst::STRATEGY_POSTGRESQL => RawPostgresQuery::class,
        };
    }

    /**
     * Requête SQL pour obtenir l'ensemble des tables de la base de données
     * @param string $schema
     * @return string
     */
    public function getQueryAllInformationSchema(string $schema): string
    {
        return $this->rawClass::getQueryAllInformationSchema($schema);
    }

    /**
     * Retourne la structure d'une table
     * @param string $table
     * @return string
     */
    public function getQueryStructureTable(string $table): string
    {
        return $this->rawClass::getQueryStructureTable($table);
    }

    /**
     * Vérifie si une table existe dans la base de données
     * @param string $schema
     * @param string $table
     * @return string
     */
    public function getQueryExistTable(string $schema, string $table): string
    {
        return $this->rawClass::getQueryExistTable($schema, $table);
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
     * @return string
     */
    public function getQueryPurgeNotification(): string
    {
        return $this->rawClass::getQueryPurgeNotification();
    }
}
