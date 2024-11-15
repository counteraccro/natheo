<?php
/**
 * Traitement des résultats des reqyêtes Mysql
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Utils\Tools\DatabaseManager\QueryResult;

use App\Utils\Tools\DatabaseManager\QueryResult\RawResultQueryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RawResultMysqlQuery implements RawResultQueryInterface
{

    /**
     * @inheritDoc
     */
    public static function getResultAllInformationSchema(array $result, TranslatorInterface $translator): array
    {
       return [];
    }

    /**
     * @inheritDoc
     */
    public static function getResultStructureTable(array $result, TranslatorInterface $translator): array
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
}
