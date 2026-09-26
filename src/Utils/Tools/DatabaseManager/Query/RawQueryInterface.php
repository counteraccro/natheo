<?php

declare(strict_types=1);
/**
 * Interface des RawQuery en fonction du SGBD
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Tools\DatabaseManager\Query;

interface RawQueryInterface
{
    /**
     * Requête SQL pour obtenir l'ensemble des tables de la base de données
     * Paramètre attendu à l'exécution : schema
     * @return string
     */
    public static function getQueryAllInformationSchema(): string;

    /**
     * Retourne la structure d'une table
     * @param string $table
     * @param string $schema
     * @return string
     */
    public static function getQueryStructureTable(string $table, string $schema = ''): string;

    /**
     * Vérifie si une table existe dans la base de données
     * Paramètres attendus à l'exécution : table, et schema si $withSchema vaut true
     * @param bool $withSchema
     * @return string
     */
    public static function getQueryExistTable(bool $withSchema = true): string;

    /**
     * Permet d'obtenir la liste des bases de données
     * @return string
     */
    public static function getQueryAllDatabase(): string;

    /**
     * Permet de purger les notifications
     * @param string $table nom de la table notification, déjà quoté
     * @return string
     */
    public static function getQueryPurgeNotification(string $table): string;

    /**
     * Check si la connexion est bonne ou non
     * @return string
     */
    public static function getQueryCheckConnexion(): string;

    /**
     * Calcul une somme de value en fonction d'une Key
     * @return string
     */
    public static function getQueryTotalStatByKey(): string;

    /**
     * Retourne les ids de page triés par la valeur (numérique) d'une statistique, du plus grand au plus petit
     * @param int $limit
     * @return string
     */
    public static function getQueryPageMostViewedByKey(int $limit): string;
}
