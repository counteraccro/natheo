<?php
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Service pour DatabaseManager
 */

namespace App\Service\Admin\Tools;

use App\Enum\Admin\Tools\DatabaseManager\DatabaseManagerData;
use App\Service\Admin\AppAdminService;
use App\Utils\Global\Database\DataBase;
use App\Utils\Utils;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

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

        $query = $rawQuery->getQueryAllInformationSchema(
            str_replace('.', '', $parameterBag->get('app.default_database_schema')),
        );
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \DateMalformedStringException
     */
    public function getAllDump(): array
    {
        $kernel = $this->getKernel();

        $finder = new Finder();
        $finder->files()->in($kernel->getProjectDir() . DatabaseManagerData::getRootPath());

        $return = [];
        foreach ($finder as $file) {
            $return[] = [
                'name' => $file->getFilename(),
                'date' => (new \DateTime('@' . $file->getFileInfo()->getCTime()))->format('d F Y, H:i'),
                'url' => '/' . DatabaseManagerData::FOLDER_NAME->value . '/' . $file->getFilename(),
                'size' => Utils::getSizeName($file->getSize()),
                'extension' => strtoupper($file->getExtension()),
            ];
        }
        return array_reverse($return);
    }

    /**
     * Supprime un fichier dump en fonction de son nom
     * @param string $filename
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function deleteDumpFile(string $filename): string
    {
        $kernel = $this->getKernel();
        $fileSystem = new Filesystem();

        try {
            $fileSystem->remove($kernel->getProjectDir() . DatabaseManagerData::getRootPath() . $filename);
        } catch (IOExceptionInterface $exception) {
            return $exception->getMessage();
        }
        return '';
    }
}
