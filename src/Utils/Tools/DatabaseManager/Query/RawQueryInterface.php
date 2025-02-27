<?php
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
     * @param string $schema
     * @return string
     */
    public static function getQueryAllInformationSchema(string $schema): string;

    /**
     * Retourne la structure d'une table
     * @param string $table
     * @return string
     */
    public static function getQueryStructureTable(string $table): string;

    /**
     * Vérifie si une table existe dans la base de données
     * @param string $schema
     * @param string $table
     * @return string
     */
    public static function getQueryExistTable(string $schema, string $table): string;

    /**
     * Permet d'obtenir la liste des bases de données
     * @return string
     */
    public static function getQueryAllDatabase(): string;

    /**
     * Permet de purger les notifications
     * @return string
     */
    public static function getQueryPurgeNotification(): string;
}
