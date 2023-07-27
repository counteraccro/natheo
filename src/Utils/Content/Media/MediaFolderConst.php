<?php
/**
 * Constante pour les dossiers médias
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Content\Media;

class MediaFolderConst
{
    /**
     * Nom du dossier root des assets
     */
    public const ROOT_FOLDER_NAME = 'public' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR;

    /**
     * Url pour le dossier asset
     */
    public const PATH_WEB_PATH = '/assets/';

    /**
     * Chemin pour le dossier miniature
     */
    public const ROOT_THUMBNAILS = 'public' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'thumbnails';

    /**
     * Url pour le dossier thumbnails
     */
    public const PATH_WEB_THUMBNAILS = '/assets/thumbnails/';
}
