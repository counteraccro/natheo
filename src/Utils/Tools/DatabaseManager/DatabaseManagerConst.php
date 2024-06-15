<?php
/**
 * Constante pour les données venant de DatabaseManag
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Utils\Tools\DatabaseManager;

class DatabaseManagerConst
{
    public const FOLDER_NAME = 'dump';

    /**
     * Nom du dossier root des dumps
     */
    public const ROOT_FOLDER_NAME = 'public' . DIRECTORY_SEPARATOR . self::FOLDER_NAME . DIRECTORY_SEPARATOR;

    /**
     * Nom du fichier
     */
    public const FILE_NAME_DUMP = 'file_dump_';

    /**
     * Extension du fichier
     */
    public const FILE_DUMP_EXTENSION = '.sql';
}

