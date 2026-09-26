<?php

declare(strict_types=1);
/**
 * Enum DatabaseManager, données sur les dumps
 * @author Gourdon Aymeric
 * @version 1.1
 */

namespace App\Enum\Admin\Tools\DatabaseManager;

enum DatabaseManagerData: string
{
    case FILE_NAME_DUMP = 'file_dump_';

    case FILE_DUMP_EXTENSION = '.sql';

    case DATA_TABLE = 'table';

    case DATA_DATA = 'data';

    case DATA_TABLE_AND_DATA = 'data_table';

    /**
     * Format autorisé pour un nom de dump (sans extension)
     */
    const string FILENAME_PATTERN = '/^[\w\-]{1,100}$/';

    /**
     * Vérifie qu'un nom de dump (sans extension) est valide
     * @param string $name
     * @return bool
     */
    static function isValidName(string $name): bool
    {
        return preg_match(self::FILENAME_PATTERN, $name) === 1;
    }

    /**
     * Vérifie qu'un nom de fichier de dump (avec extension) est valide
     * @param string $filename
     * @return bool
     */
    static function isValidFileName(string $filename): bool
    {
        if (!str_ends_with($filename, self::FILE_DUMP_EXTENSION->value)) {
            return false;
        }
        return self::isValidName(substr($filename, 0, -strlen(self::FILE_DUMP_EXTENSION->value)));
    }

    /**
     * Liste des types de données possibles pour un dump
     * @return string[]
     */
    static function getDataTypes(): array
    {
        return [self::DATA_TABLE->value, self::DATA_DATA->value, self::DATA_TABLE_AND_DATA->value];
    }
}
