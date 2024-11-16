<?php
/**
 * Traitement des résultats des reqyêtes Mysql
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Tools\DatabaseManager\QueryResult;

use App\Utils\Tools\DatabaseManager\QueryResult\RawResultQueryInterface;
use App\Utils\Utils;
use Symfony\Contracts\Translation\TranslatorInterface;

class RawResultMysqlQuery implements RawResultQueryInterface
{

    /**
     * @inheritDoc
     */
    public static function getResultAllInformationSchema(array $result, TranslatorInterface $translator): array
    {
        $newHeader = [
            'schema' => $translator->trans('database_manager.schema.all.bdd.row.schema', domain: 'database_manager'),
            'table_name' => $translator->trans('database_manager.schema.all.bdd.row.table_name', domain: 'database_manager'),
            'row' => $translator->trans('database_manager.schema.all.bdd.row.row', domain: 'database_manager'),
            'size' => $translator->trans('database_manager.schema.all.bdd.row.size', domain: 'database_manager')
        ];

        $nbElement = 0;
        $sizeBite = 0;
        $nbTable = 0;
        foreach ($result['result'] as &$row) {
            if (intval($row['row']) !== -1) {
                $nbElement += intval($row['row']);
            } else {
                $row['row'] = $translator->trans('database_manager.schema.all.bdd.error_row.',
                    domain: 'database_manager');
            }
            $sizeBite += intval($row['size']);
            $row['size'] = Utils::getSizeName($row['size']);
            $nbTable++;
        }

        $result['header'] = $newHeader;
        $result['stat'] = [
            'nbElement' => $nbElement,
            'sizeBite' => Utils::getSizeName($sizeBite),
            'nbTable' => $nbTable,
        ];

        return $result;
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
