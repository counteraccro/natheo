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
        $rawQuery = $this->getRawQueryManager();
        $rawResultQuery = $this->getRawResultQueryManager();

        $translator = $this->getTranslator();
        $parameterBag = $this->getParameterBag();

        $query = $rawQuery->getQueryAllInformationSchema(str_replace('.', '',$parameterBag->get('app.default_database_schema')));
        $result = $database->executeRawQuery($query);

        return $rawResultQuery->getResultAllInformationSchema($result, $translator);
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
        $rawQuery = $this->getRawQueryManager();
        $rawResultQuery = $this->getRawResultQueryManager();
        $translator = $this->getTranslator();

        $query = $rawQuery->getQueryStructureTable($tableName);

        $result = $database->executeRawQuery($query);
        $result = $rawResultQuery->getResultStructureTable($result, $translator);
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
