<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service pour DatabaseManager
 */

namespace App\Service\Admin\Tools;

use App\Service\Admin\AppAdminService;
use App\Utils\Global\Database\DataBase;
use App\Utils\Tools\DatabaseManager\DatabaseManagerConst;
use App\Utils\Tools\DatabaseManager\Query\RawPostgresQuery;
use App\Utils\Utils;
use Nette\Utils\Finder;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class DatabaseManagerService extends AppAdminService
{

    /**
     * Retourne les informations de la base de données
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllInformationSchemaDatabase(): array
    {
        /** @var DataBase $database */
        $database = $this->handlers->get('database');
        $translator = $this->getTranslator();
        $parameterBag = $this->getParameterBag();
        $query = RawPostgresQuery::getQueryAllInformationSchema(
            str_replace('.', '',$parameterBag->get('app.default_database_schema'))
        );
        $result = $database->executeRawQuery($query);

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
     * Retourne le schema d'une table
     * @param string $tableName
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getSchemaTableByTable(string $tableName): array
    {
        $database = $this->handlers->get('database');
        $translator = $this->getTranslator();
        $query = RawPostgresQuery::getQueryStructureTable($tableName);

        $result = $database->executeRawQuery($query);

        $newHeader = [];
        foreach ($result['header'] as $header) {

            $newHeader[$header] = $translator->trans('database_manager.schema.table.row.' . $header,
                domain: 'database_manager');
        }

        $result['header'] = $newHeader;
        $result['table'] = $tableName;
        return $result;
    }

    /**
     * Retourne la liste des dumps SQL
     * @return array
     */
    public function getAllDump(): array
    {
        $finder = new Finder();
        $finder->files()->in('../' . DatabaseManagerConst::ROOT_FOLDER_NAME);

        $return = [];
        foreach ($finder as $file) {

            $return[] = [
                'name' => $file->getFilename(),
                'url' => '/' . DatabaseManagerConst::FOLDER_NAME . '/' . $file->getFilename()
            ];
        }
        return array_reverse($return);
    }
}
