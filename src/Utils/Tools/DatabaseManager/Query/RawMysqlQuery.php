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
        return "";
    }

    /**
     * @inheritDoc
     */
    public static function getQueryStructureTable(string $table): string
    {
        return "";
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
}
