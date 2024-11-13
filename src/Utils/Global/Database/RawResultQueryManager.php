<?php
/**
 * Manager qui permet d'interpréter le résultat des RawQuery en fonction du SGBD
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Global\Database;

use App\Utils\Installation\InstallationConst;
use App\Utils\Tools\DatabaseManager\QueryResult\RawResultMysqlQuery;
use App\Utils\Tools\DatabaseManager\QueryResult\RawResultPostgresQuery;

class RawResultQueryManager
{
    /**
     * @var mixed|string
     */
    private mixed $rawClass;

    public function __construct()
    {
        $this->rawClass = match (InstallationConst::STRATEGY)
        {
            InstallationConst::STRATEGY_MYSQL => RawResultMysqlQuery::class,
            InstallationConst::STRATEGY_POSTGRESQL => RawResultPostgresQuery::class,
        };
    }

    /**
     * Retourne de façon formaté l'ensemble des informations de la bdd
     * @param array $result
     * @return array
     */
    public function getResultAllInformationSchema(array $result): array
    {
        return $this->rawClass::getResultAllInformationSchema($result);
    }

    /**
     * Retourne la structure d'une table
     * @param array $result
     * @return array
     */
    public function getResultStructureTable(array $result): array
    {
        return $this->rawClass::getResultStructureTable($result);
    }

    /**
     * Renvoi true si la table existe, false sinon
     * @param array $result
     * @return bool
     */
    public function getResultExistTable(array $result): bool
    {
        return $this->rawClass::getResultExistTable($result);
    }

    /**
     * Permet d'obtenir la liste des bases de données
     * @param array $result
     * @return array
     */
    public function getResultAllDatabase(array $result): array
    {
        return $this->rawClass::getResultAllDatabase($result);
    }
}