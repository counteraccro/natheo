<?php
/**
 * Traitement des résultats des reqyêtes Mysql
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Utils\Tools\DatabaseManager\QueryResult;

use App\Utils\Tools\DatabaseManager\QueryResult\RawResultQueryInterface;

class RawResultMysqlQuery implements RawResultQueryInterface
{

    /**
     * @inheritDoc
     */
    public static function getResultAllInformationSchema(array $result): array
    {
       return [];
    }

    /**
     * @inheritDoc
     */
    public static function getResultStructureTable(array $result): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public static function getResultExistTable(array $result): bool
    {
        if (isset($result['result'][0]['TABLE_NAME'])) {
            return true;
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public static function getResultAllDatabase(array $result): array
    {
        return [];
    }
}
