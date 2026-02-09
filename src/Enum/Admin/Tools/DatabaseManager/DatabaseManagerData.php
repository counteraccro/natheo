<?php
/**
 * Enum DatabaseManager, données sur les dumps
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\Tools\DatabaseManager;

enum DatabaseManagerData: string
{
    case FOLDER_NAME = 'dump';

    case FILE_NAME_DUMP = 'file_dump_';

    case FILE_DUMP_EXTENSION = '.sql';

    /**
     * Path du stockage des dumps
     * @return string
     */
    static function getRootPath(): string
    {
        return DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . self::FOLDER_NAME->value . DIRECTORY_SEPARATOR;
    }
}
