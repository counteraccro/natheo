<?php
/**
 * Class Qui permet d'obtenir des informations sur la base de donnée
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Global;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;

class DataBase
{
    /**
     * @var EntityManagerInterface|mixed
     */
    protected EntityManagerInterface $entityManager;

    public function __construct(#[AutowireLocator([
        'entityManager' => EntityManagerInterface::class,
    ])] private readonly ContainerInterface $handlers)
    {
        $this->entityManager = $this->handlers->get('entityManager');
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
     * @throws Exception
     */
    public function executeRawQuery(string $query): array
    {
        try {
            $statement = $this->entityManager->getConnection()->prepare($query);
        } catch (Exception $e) {


            return [
                'result' => [],
                'error' => $e->getMessage()
            ];
        }

        try {
            $result = $statement->executeQuery()->fetchAllAssociative();
        } catch (Exception $e) {
            return [
                'result' => [],
                'error' => $e->getMessage(),
            ];
        }

        return [
            'result' => $result,
            'error' => '',
        ];

    }
}
