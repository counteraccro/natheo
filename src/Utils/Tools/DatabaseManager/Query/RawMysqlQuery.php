<?php
/**
 * Class Qui permet d'obtenir des requêtes SQL venant de mysql
 * @author Gourdon Aymeric
 * @version 1.1
 */
namespace App\Utils\Tools\DatabaseManager\Query;

use App\Utils\Tools\DatabaseManager\Query\RawQueryInterface;

class RawMysqlQuery implements RawQueryInterface
{

    /**
     * @inheritDoc
     */
    public static function getQueryAllInformationSchema(string $schema): string
    {
        return "SELECT table_schema as 'schema', table_name AS 'table_name',
                table_rows as 'row',
                (data_length + index_length) AS 'size'
                FROM information_schema.TABLES
                WHERE table_schema = '" . $schema . "'
                ORDER BY table_name ASC;";
    }

    /**
     * @inheritDoc
     */
    public static function getQueryStructureTable(string $table): string
    {
        return "DESCRIBE " . $table . ";";
    }

    /**
     * @inheritDoc
     */
    public static function getQueryExistTable(string $schema, string $table): string
    {
        return "SELECT TABLE_NAME 
                FROM INFORMATION_SCHEMA.TABLES 
                WHERE TABLE_SCHEMA = '" . $schema . "' 
                AND TABLE_NAME = '". $table ."';
        ";
    }

    /**
     * @inheritDoc
     */
    public static function getQueryAllDatabase(): string
    {
        return "SHOW DATABASES;";
    }

    /**
     * @return string
     */
    public static function getQueryPurgeNotification(): string
    {
        return "DELETE
                FROM natheo.notification n
                WHERE n.user_id = :user_id
                    AND n.read = 1
                    AND DATEDIFF(CURRENT_DATE(), n.created_at) > :nb_day";
    }
}
