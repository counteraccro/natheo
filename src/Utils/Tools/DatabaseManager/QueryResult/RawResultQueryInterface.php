<?php
/**
 * Interface du traitement des résultats de RawQuery en fonction du SGBD
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Utils\Tools\DatabaseManager\QueryResult;

interface RawResultQueryInterface
{
    /**
     * Retourne de façon formaté l'ensemble des informations de la bdd
     * @param array $result
     * @return array
     */
    public static function getResultAllInformationSchema(array $result): array;

    /**
     * Retourne la structure d'une table
     * @param array $result
     * @return array
     */
    public static function getResultStructureTable(array $result): array;

    /**
     * Renvoi true si la table existe, false sinon
     * @param array $result
     * @return bool
     */
    public static function getResultExistTable(array $result): bool;

    /**
     * Permet d'obtenir la liste des bases de données
     * @param array $result
     * @return array
     */
    public static function getResultAllDatabase(array $result): array;
}
