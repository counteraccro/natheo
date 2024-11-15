<?php
/**
 * Interface du traitement des résultats de RawQuery en fonction du SGBD
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Utils\Tools\DatabaseManager\QueryResult;

use Symfony\Contracts\Translation\TranslatorInterface;

interface RawResultQueryInterface
{
    /**
     * Retourne de façon formaté l'ensemble des informations de la bdd
     * @param array $result
     * @param TranslatorInterface $translator
     * @return array
     */
    public static function getResultAllInformationSchema(array $result, TranslatorInterface $translator): array;

    /**
     * Retourne la structure d'une table
     * @param array $result
     * @param TranslatorInterface $translator
     * @return array
     */
    public static function getResultStructureTable(array $result, TranslatorInterface $translator): array;

    /**
     * Renvoi true si la table existe, false sinon
     * @param array $result
     * @return bool
     */
    public static function getResultExistTable(array $result): bool;
}
