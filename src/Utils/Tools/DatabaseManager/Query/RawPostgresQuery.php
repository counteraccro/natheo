<?php
/**
 * Class Qui permet d'obtenir des requêtes SQL venant de postgres
 * @author Gourdon Aymeric
 * @version 1.1
 */

namespace App\Utils\Tools\DatabaseManager\Query;

class RawPostgresQuery implements RawQueryInterface
{
    /**
     * Requête SQL pour obtenir l'ensemble des tables de la base de données
     * @param String $schema
     * @return string
     */
    public static function getQueryAllInformationSchema(string $schema): string
    {
        return "WITH RECURSIVE pg_inherit(inhrelid, inhparent) AS
                   (select inhrelid, inhparent
                    FROM pg_inherits
                    UNION
                    SELECT child.inhrelid, parent.inhparent
                    FROM pg_inherit child, pg_inherits parent
                    WHERE child.inhparent = parent.inhrelid),
               pg_inherit_short AS (SELECT * FROM pg_inherit WHERE inhparent NOT IN (SELECT inhrelid FROM pg_inherit))
                SELECT table_schema as schema
                     , TABLE_NAME as  table_name
                     , row_estimate as row
                     , pg_size_pretty(total_bytes) as size
                     , total_bytes
                     --, pg_size_pretty(index_bytes) AS INDEX
                     --, pg_size_pretty(toast_bytes) AS toast
                     --, pg_size_pretty(table_bytes) AS TABLE
                     --, total_bytes::float8 / sum(total_bytes) OVER () AS total_size_share
                FROM (
                         SELECT *, total_bytes-index_bytes-COALESCE(toast_bytes,0) AS table_bytes
                         FROM (
                                  SELECT c.oid
                                       , nspname AS table_schema
                                       , relname AS TABLE_NAME
                                       , SUM(c.reltuples) OVER (partition BY parent) AS row_estimate
                                       , SUM(pg_total_relation_size(c.oid)) OVER (partition BY parent) AS total_bytes
                                       , SUM(pg_indexes_size(c.oid)) OVER (partition BY parent) AS index_bytes
                                       , SUM(pg_total_relation_size(reltoastrelid)) OVER (partition BY parent) AS toast_bytes
                                       , parent
                                  FROM (
                                           SELECT pg_class.oid
                                                , reltuples
                                                , relname
                                                , relnamespace
                                                , pg_class.reltoastrelid
                                                , COALESCE(inhparent, pg_class.oid) parent
                                           FROM pg_class
                                                    LEFT JOIN pg_inherit_short ON inhrelid = oid
                                           WHERE relkind IN ('r', 'p')
                
                                       ) c
                                           LEFT JOIN pg_namespace n ON n.oid = c.relnamespace
                              ) a
                         WHERE oid = parent
                         AND table_schema = '" .
            $schema .
            "'
                     ) a
                ORDER BY TABLE_NAME ASC";
    }

    /**
     * Retourne la structure d'une table
     * @param string $table
     * @return string
     */
    public static function getQueryStructureTable(string $table): string
    {
        return "SELECT
            column_name,
            data_type,
            character_maximum_length,
            is_nullable,
            column_default
        FROM
            information_schema.columns
        WHERE
            table_name = '" .
            $table .
            "'";
    }

    /**
     * Vérifie si une table existe dans la base de données
     * @param string $schema
     * @param string $table
     * @return string
     */
    public static function getQueryExistTable(string $schema, string $table): string
    {
        return "SELECT EXISTS (
            SELECT FROM information_schema.tables WHERE  table_schema = '" .
            $schema .
            "'
            AND table_name   = '" .
            $table .
            "'
            )";
    }

    /**
     * Permet d'obtenir la liste des bases de données
     * @return string
     */
    public static function getQueryAllDatabase(): string
    {
        return 'SELECT * FROM pg_database';
    }

    /**
     * Permet de purger les notifications
     * @return string
     */
    public static function getQueryPurgeNotification(): string
    {
        return 'DELETE
            FROM natheo.notification n
            WHERE n.user_id = :user_id
            AND n.read = true
            AND EXTRACT(day from ((CURRENT_DATE - n.created_at))) > :nb_day';
    }
}
