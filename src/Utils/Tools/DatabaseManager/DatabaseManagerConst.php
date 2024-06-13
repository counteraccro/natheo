<?php
/**
 * Constante pour les données venant de DatabaseManag
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Utils\Tools\DatabaseManager;

class DatabaseManagerConst
{
    /**
     * Nom du dossier root des dumps
     */
    public const ROOT_FOLDER_NAME = 'public' . DIRECTORY_SEPARATOR . 'dump' . DIRECTORY_SEPARATOR;

    public const FILE_NAME_DUMP = 'file_dump_';

    public const FILE_DUMP_EXTENSION = '.sql';
}

