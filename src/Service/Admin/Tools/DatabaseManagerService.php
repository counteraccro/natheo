<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.2
 * Service pour DatabaseManager
 */

namespace App\Service\Admin\Tools;

use App\Enum\Admin\Tools\DatabaseManager\DatabaseManagerData;
use App\Service\Admin\AppAdminService;
use App\Utils\Utils;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Name\OptionallyQualifiedName;
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
        $database = $this->getDatabase();
        $rawQuery = $this->getRawQueryManager();
        $rawResultQuery = $this->getRawResultQueryManager();

        $query = $rawQuery->getQueryAllInformationSchema();
        $result = $database->executeRawQuery($query, ['schema' => $this->getSchemaName()]);

        return $rawResultQuery->getResultAllInformationSchema($result, $this->getTranslator());
    }

    /**
     * Retourne le schema d'une table
     * @param string $tableName
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function getSchemaTableByTable(string $tableName): array
    {
        $translator = $this->getTranslator();

        // Le nom de table est concaténé dans une requête brute, il doit être un nom réel de la base
        if (!in_array($tableName, $this->getAllTableNames(), true)) {
            return [
                'result' => [],
                'header' => [],
                'error' => $translator->trans(
                    'database_manager.error.table.not.found',
                    ['table' => $tableName],
                    domain: 'database_manager',
                ),
                'table' => '',
            ];
        }

        $query = $this->getRawQueryManager()->getQueryStructureTable($tableName, $this->getSchemaName());
        $result = $this->getDatabase()->executeRawQuery($query);
        $result = $this->getRawResultQueryManager()->getResultStructureTable($result, $translator);
        $result['table'] = $tableName;
        return $result;
    }

    /**
     * Retourne la liste des dumps SQL, du plus récent au plus ancien
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllDump(): array
    {
        $path = $this->getDumpDirectory();
        if (!(new Filesystem())->exists($path)) {
            return [];
        }

        $finder = new Finder();
        $finder
            ->files()
            ->in($path)
            ->name('*' . DatabaseManagerData::FILE_DUMP_EXTENSION->value)
            ->sortByModifiedTime()
            ->reverseSorting();

        $router = $this->getRouter();
        $return = [];
        foreach ($finder as $file) {
            $return[] = [
                'name' => $file->getFilename(),
                'date' => (new \DateTime('@' . $file->getMTime()))
                    ->setTimezone(new \DateTimeZone(date_default_timezone_get()))
                    ->format('d/m/Y H:i'),
                'url' => $router->generate('admin_database_manager_download_dump_file', [
                    'filename' => $file->getFilename(),
                ]),
                'size' => Utils::getSizeName($file->getSize()),
                'extension' => strtoupper($file->getExtension()),
            ];
        }
        return $return;
    }

    /**
     * Retourne le chemin complet d'un dump existant, null si le nom est invalide ou si le fichier n'existe pas
     * @param string $filename
     * @return string|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getDumpFilePath(string $filename): ?string
    {
        if (!DatabaseManagerData::isValidFileName($filename)) {
            return null;
        }

        $path = $this->getDumpDirectory() . $filename;
        if (!is_file($path)) {
            return null;
        }
        return $path;
    }

    /**
     * Indique si un dump portant ce nom (sans extension) existe déjà
     * @param string $name
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function isDumpExist(string $name): bool
    {
        return is_file($this->getDumpDirectory() . $name . DatabaseManagerData::FILE_DUMP_EXTENSION->value);
    }

    /**
     * Supprime un fichier dump en fonction de son nom
     * @param string $filename
     * @return string message d'erreur, vide si la suppression a réussi
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function deleteDumpFile(string $filename): string
    {
        $path = $this->getDumpFilePath($filename);
        if ($path === null) {
            return $this->getTranslator()->trans(
                'database_manager.error.delete.file.dump',
                ['fichier' => $filename],
                domain: 'database_manager',
            );
        }

        try {
            (new Filesystem())->remove($path);
        } catch (IOExceptionInterface $exception) {
            $this->getLogger()->error($exception->getMessage());
            return $this->getTranslator()->trans(
                'database_manager.error.delete.file.dump',
                ['fichier' => $filename],
                domain: 'database_manager',
            );
        }
        return '';
    }

    /**
     * Retourne le dossier de stockage des dumps
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getDumpDirectory(): string
    {
        return $this->getParameterBag()->get('app.dump_directory');
    }

    /**
     * Retourne le nom du schéma sans le séparateur
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getSchemaName(): string
    {
        return str_replace('.', '', (string) $this->getParameterBag()->get('app.default_database_name'));
    }

    /**
     * Retourne le nom (non qualifié) de l'ensemble des tables de la base de données
     * @return string[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    private function getAllTableNames(): array
    {
        $schemaManager = $this->getEntityManager()->getConnection()->createSchemaManager();

        return array_map(
            static fn(OptionallyQualifiedName $name): string => $name->getUnqualifiedName()->getValue(),
            $schemaManager->introspectTableNames(),
        );
    }
}
