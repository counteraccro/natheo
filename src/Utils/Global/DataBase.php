<?php
/**
 * Class Qui permet d'obtenir des informations sur la base de donnée
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Global;

use App\Utils\Tools\DatabaseManager\Query\RawPostgresQuery;
use App\Utils\Utils;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
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

    protected Connection $connection;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(#[AutowireLocator([
        'entityManager' => EntityManagerInterface::class,
        'connexion' => Connection::class,
        'parameterBag' => ParameterBagInterface::class,
    ])] private readonly ContainerInterface $handlers)
    {
        $this->entityManager = $this->handlers->get('entityManager');
        $this->connection = $this->handlers->get('connexion');
    }

    /**
     * Détecte si la base de données est connecté ou non
     * @return bool
     */
    public function isConnected(): bool
    {
        $query = RawPostgresQuery::getQueryAllDatabase();
        $this->executeRawQuery($query);
        return $this->entityManager->getConnection()->isConnected();
    }

    /**
     * Vérifie si la table existe
     * @param string|null $tableName nom de la table sans préfix
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function isTableExiste(string $tableName = null): bool
    {
        /** @var ParameterBagInterface $parameterBag */
        $parameterBag = $this->handlers->get('parameterBag');
        $prefix = $parameterBag->get('app.default_database_prefix');

        if ($tableName === null) {
            if ($prefix !== "") {
                $prefix .= "_";
            }
            $tableName = $prefix . 'user';
        }

        $query = RawPostgresQuery::getQueryExistTable('natheo', $tableName);

        $result = $this->executeRawQuery($query);
        if (isset($result['result'][0]['exists'])) {
            return $result['result'][0]['exists'];
        }
        return false;

    }

    /**
     * Vérifie si des données existent en fonction du modèle
     * @param string $entity
     * @return bool
     */
    public function isDataInTable(string $entity): bool
    {
        $values = $this->entityManager->getRepository($entity)->findAll();
        if(empty($values)) {
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
        $schema = $parameterBag->get('app.default_database_schema');
        if (str_contains($schema, '.')) {
            $schema = str_replace('.', '', $schema);
        }

        try {
            $schemaManager = $this->connection->createSchemaManager();
            if ($schema !== "" && !in_array($schema, $schemaManager->listDatabases())) {
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
        $array = array_map(
            function (ClassMetadata $meta) {
                return [
                    'name' => $meta->getTableName(),
                    'columns' => $meta->getFieldNames(),
                    'assocationMapping' => $meta->getAssociationMappings()
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
        $array = array_map(
            function (ClassMetadata $meta) use ($entity) {
                if ($entity === $meta->getName()) {
                    return [
                        'name' => $meta->getTableName(),
                        'column' => $meta->getFieldNames()
                    ];
                }
                return null;
            }, $allMetadata);

        return array_values(array_filter($array))[0];
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
                'error' => $e->getMessage()
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
