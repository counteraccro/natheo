<?php
/**
 * Traitement des résultats des reqyêtes Postgres
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Utils\Tools\DatabaseManager\QueryResult;

use App\Utils\Tools\DatabaseManager\QueryResult\RawResultQueryInterface;
use App\Utils\Utils;
use Symfony\Contracts\Translation\TranslatorInterface;

class RawResultPostgresQuery implements RawResultQueryInterface
{

    /**
     * @inheritDoc
     */
    public static function getResultAllInformationSchema(array $result, TranslatorInterface $translator): array
    {
        $newHeader = [];
        foreach ($result['header'] as $header) {
            if ($header === 'total_bytes') {
                continue;
            }
            $newHeader[$header] = $translator->trans('database_manager.schema.all.bdd.row.' . $header,
                domain: 'database_manager');
        }

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
            $sizeBite += intval($row['total_bytes']);
            unset($row['total_bytes']);
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
        $newHeader = [];
        foreach ($result['header'] as $header) {

            $newHeader[$header] = $translator->trans('database_manager.schema.table.row.' . $header,
                domain: 'database_manager');
        }

        $result['header'] = $newHeader;
        return $result;
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
}
