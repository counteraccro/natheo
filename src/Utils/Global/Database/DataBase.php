<?php

declare(strict_types=1);
/**
 * Class Qui permet d'obtenir des informations sur la base de donnée
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Global\Database;

use App\Enum\Installation\DoctrineStrategy;
use App\Enum\Installation\OptionInstallation;
use App\Service\Installation\InstallationService;
use App\Utils\Tools\DatabaseManager\Query\RawPostgresQuery;
use App\Utils\Utils;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class DataBase
{
    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $entityManager;

    /**
     * @var Connection|mixed
     */
    protected Connection $connection;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        #[
            AutowireLocator([
                'entityManager' => EntityManagerInterface::class,
                'connexion' => Connection::class,
                'parameterBag' => ParameterBagInterface::class,
                'rawQueryManager' => RawQueryManager::class,
                'rawResultQueryManager' => RawResultQueryManager::class,
                'installationService' => InstallationService::class,
            ]),
        ]
        private readonly ContainerInterface $handlers,
    ) {
        $this->entityManager = $this->handlers->get('entityManager');
        $this->connection = $this->handlers->get('connexion');
    }

    /**
     * Détecte si la base de données est connecté ou non
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function isDatabaseExist(array $parameters = []): array
    {
        /** @var RawQueryManager $rawQueryManager */
        $rawQueryManager = $this->handlers->get('rawQueryManager');

        try {
            $connection = DriverManager::getConnection($parameters);
            $connection->executeQuery($rawQueryManager->getQueryAllDatabase());
            $connection->close();

            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function isConnected(array $parameters = []): array
    {
        unset($parameters['dbname']);
        /** @var RawQueryManager $rawQueryManager */
        $rawQueryManager = $this->handlers->get('rawQueryManager');

        try {
            $connection = DriverManager::getConnection($parameters);
            $connection->executeQuery($rawQueryManager->getQueryCheckConnexion());
            $connection->close();

            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Permet de tester une connexion en fonction d'une action
     * @param string $action
     * @param array $parameters
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function check(string $action, array $parameters = []): array
    {
        /** @var InstallationService $installationService */
        $installationService = $this->handlers->get('installationService');

        $params = [];
        if (empty($parameters)) {
            $parameters = $installationService->getDatabaseUrl();
        }
        $params = [
            'host' => $parameters['ip'],
            'port' => $parameters['port'],
            'user' => $parameters['login'],
            'password' => $parameters['password'],
            'driver' => DoctrineStrategy::getDriver(DoctrineStrategy::current()),
            'dbname' => $parameters['bdd_name'],
        ];

        return match ($action) {
            OptionInstallation::CONNEXION->value => $this->isConnected($params),
            OptionInstallation::DATABASE_EXIST->value => $this->isDatabaseExist($params),
        };
    }

    /**
     * Vérifie si la table existe
     * @param string|null $tableName nom de la table sans préfix
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function isTableExiste(?string $tableName = null): bool
    {
        /** @var ParameterBagInterface $parameterBag */
        $parameterBag = $this->handlers->get('parameterBag');
        $prefix = $parameterBag->get('app.default_database_prefix');
        $schema = $parameterBag->get('app.default_database_name');

        if ($tableName === null) {
            if ($prefix !== '') {
                $prefix .= '_';
            }
            $tableName = $prefix . 'user';
        }

        /** @var RawQueryManager $rawQueryManager */
        $rawQueryManager = $this->handlers->get('rawQueryManager');
        $query = $rawQueryManager->getQueryExistTable($schema, $tableName);

        $result = $this->executeRawQuery($query);

        /** @var RawResultQueryManager $rawResultQueryManager */
        $rawResultQueryManager = $this->handlers->get('rawResultQueryManager');

        return $rawResultQueryManager->getResultExistTable($result);
    }

    /**
     * Vérifie si des données existent en fonction du modèle
     * @param string $entity
     * @return bool
     */
    public function isDataInTable(string $entity): bool
    {
        $values = $this->entityManager->getRepository($entity)->findAll();
        if (empty($values)) {
            return false;
        }
        return true;
    }

    /**
     * test si le schema existe
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function isSchemaExist(): bool
    {
        /** @var ParameterBagInterface $parameterBag */
        $parameterBag = $this->handlers->get('parameterBag');
        $schema = $parameterBag->get('app.default_database_name');

        if (empty($schema)) {
            return true;
        }

        if (str_contains($schema, '.')) {
            $schema = str_replace('.', '', $schema);
        }

        try {
            $schemaManager = $this->connection->createSchemaManager();

            $databaseNames = array_map(
                static fn(UnqualifiedName $name): string => $name->getIdentifier()->getValue(),
                $schemaManager->introspectDatabaseNames(),
            );

            if ($schema !== '' && !in_array($schema, $databaseNames, true)) {
                return false;
            }
        } catch (Exception) {
            return false;
        }
        return true;
    }

    /**
     * Retourne l'ensemble des tables ainsi que leurs colonnes respectives triées par ordre alphabétique
     * @return array|array[]
     */
    public function getAllNameAndColumn(): array
    {
        $allMetadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $array = array_map(function (ClassMetadata $meta) {
            return [
                'name' => $meta->getTableName(),
                'columns' => $meta->getFieldNames(),
                'assocationMapping' => $meta->getAssociationMappings(),
            ];
        }, $allMetadata);

        $array = $this->mergeAssociationColumnsInColumns($array);
        $array = $this->convertFieldCamelCaseToSnakeCase($array);

        $tabName = array_column($array, 'name');
        array_multisort($tabName, SORT_ASC, $array);
        return $array;
    }

    /**
     * Merge les champs d'associations dans le tableau de champs
     * @param array $tables
     * @return array
     */
    private function mergeAssociationColumnsInColumns(array $tables): array
    {
        foreach ($tables as &$table) {
            foreach ($table['assocationMapping'] as $associationMapping) {
                if (isset($associationMapping['joinColumnFieldNames'])) {
                    $i = 1;
                    foreach ($associationMapping['joinColumnFieldNames'] as $field) {
                        array_splice($table['columns'], $i, 0, $field);
                        $i++;
                    }
                }
                unset($table['assocationMapping']);
            }
        }
        return $tables;
    }

    /**
     * Convertie les noms des champs en snake_case
     * @param array $tables
     * @return array
     */
    private function convertFieldCamelCaseToSnakeCase(array $tables): array
    {
        foreach ($tables as &$table) {
            foreach ($table['columns'] as $key => $column) {
                $table['columns'][$key] = Utils::convertCamelCaseToSnakeCase($column);
            }
        }
        return $tables;
    }

    /**
     * Retourne le nom et les columns de la table en fonction de son entity
     * @param string $entity
     * @return array|array[]|null[]
     */
    public function getNameAndColumByEntity(string $entity): array
    {
        $allMetadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $array = array_map(function (ClassMetadata $meta) use ($entity) {
            if ($entity === $meta->getName()) {
                return [
                    'name' => $meta->getTableName(),
                    'column' => $meta->getFieldNames(),
                ];
            }
            return null;
        }, $allMetadata);

        if (isset(array_values(array_filter($array))[0])) {
            return array_values(array_filter($array))[0];
        }
        return [];
    }

    /**
     * @param string $query
     * @return array
     */
    public function executeRawQuery(string $query): array
    {
        try {
            $statement = $this->entityManager->getConnection()->prepare($query);
        } catch (Exception $e) {
            return [
                'result' => [],
                'header' => [],
                'error' => $e->getMessage(),
            ];
        }

        try {
            $result = $statement->executeQuery()->fetchAllAssociative();
        } catch (Exception $e) {
            return [
                'result' => [],
                'header' => [],
                'error' => $e->getMessage(),
            ];
        }

        $header = [];
        if (!empty($result)) {
            $header = array_keys($result[0]);
        }

        return [
            'result' => $result,
            'header' => $header,
            'error' => '',
        ];
    }
}
