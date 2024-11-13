<?php
/**
 * Traitement des résultats des reqyêtes Postgres
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Utils\Tools\DatabaseManager\QueryResult;

use App\Utils\Tools\DatabaseManager\QueryResult\RawResultQueryInterface;

class RawResultPostgresQuery implements RawResultQueryInterface
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
        if (isset($result['result'][0]['exists'])) {
            return $result['result'][0]['exists'];
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
