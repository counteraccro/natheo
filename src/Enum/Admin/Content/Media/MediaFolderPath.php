<?php

declare(strict_types=1);
/**
 * Chemins et noms des dossiers de la médiathèque
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\Content\Media;

enum MediaFolderPath: string
{
    case ROOT_FOLDER_NAME = 'public' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR;

    case PATH_WEB_PATH = '/assets/';

    case ROOT_THUMBNAILS = 'public' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'thumbnails';

    case PATH_WEB_THUMBNAILS = '/assets/thumbnails/';

    case PATH_WEB_NATHEO_MEDIA = '/assets/natheo/mediatheque/';

    case NAME_DEFAULT_FOLDER_MEDIATHEQUE = 'natheotheque';

    case NAME_DEFAULT_FOLDER_MEDIATHEQUE_TEST = 'natheotheque-test';

    /**
     * Dossier public qui contient le stockage des médias, relatif au projet
     */
    case STORAGE_PUBLIC_ROOT = 'public/assets';

    /**
     * Chemin web du dossier public
     */
    case STORAGE_WEB_ROOT = '/assets';

    /**
     * Dossier de la corbeille, jamais servi, relatif au projet
     */
    case STORAGE_TRASH_ROOT = 'var/media-trash';

    /**
     * Dossier des miniatures et couvertures dans le stockage
     */
    case STORAGE_THUMBNAILS = 'thumbnails';
}
