<?php

declare(strict_types=1);
/**
 * Class Qui permet d'obtenir des requêtes SQL venant de postgres
 * @author Gourdon Aymeric
 * @version 1.1
 */

namespace App\Utils\Tools\DatabaseManager\Query;

class RawPostgresQuery implements RawQueryInterface
{
    /**
     * @inheritDoc
     */
    public static function getQueryAllInformationSchema(): string
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
                         AND table_schema = :schema
                     ) a
                ORDER BY TABLE_NAME ASC";
    }

    /**
     * @inheritDoc
     */
    public static function getQueryStructureTable(string $table, string $schema = ''): string
    {
        $sqlSchema = '';
        if ($schema !== '') {
            $sqlSchema = " AND table_schema = '" . str_replace("'", "''", $schema) . "'";
        }

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
            str_replace("'", "''", $table) .
            "'" .
            $sqlSchema .
            ' ORDER BY ordinal_position';
    }

    /**
     * @inheritDoc
     */
    public static function getQueryExistTable(bool $withSchema = true): string
    {
        $sqlSchema = $withSchema ? ':schema' : 'current_schema()';

        return 'SELECT EXISTS (
            SELECT FROM information_schema.tables WHERE table_schema = ' .
            $sqlSchema .
            '
            AND table_name = :table
            )';
    }

    /**
     * @inheritDoc
     */
    public static function getQueryAllDatabase(): string
    {
        return 'SELECT * FROM pg_database';
    }

    /**
     * @inheritDoc
     */
    public static function getQueryPurgeNotification(string $table): string
    {
        return 'DELETE
            FROM ' .
            $table .
            ' n
            WHERE n.user_id = :user_id
            AND n.read = true
            AND EXTRACT(day from ((CURRENT_DATE - n.created_at))) > :nb_day';
    }

    public static function getQueryCheckConnexion(): string
    {
        return 'SELECT 1';
    }

    /**
     * @inheritDoc
     */
    public static function getQueryTotalStatByKey(): string
    {
        return 'SELECT SUM(CAST(ps.value AS INTEGER)) AS nb
                FROM page_statistique ps
                JOIN page p ON p.id = ps.page_id
                WHERE ps.key = :key
                AND p.status = :status';
    }

    /**
     * @inheritDoc
     */
    public static function getQueryPageMostViewedByKey(int $limit): string
    {
        return 'SELECT ps.page_id AS page_id, CAST(ps.value AS INTEGER) AS nb
                FROM page_statistique ps
                WHERE ps.key = :key
                ORDER BY nb DESC
                LIMIT ' . $limit;
    }
}
