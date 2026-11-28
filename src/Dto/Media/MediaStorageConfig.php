<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Emplacements du stockage des médias et construction de leurs URL
 */

namespace App\Dto\Media;

use App\Enum\Admin\Content\Media\MediaFolderPath;

final readonly class MediaStorageConfig
{
    /**
     * @param string $storageRoot dossier absolu des fichiers servis
     * @param string $trashRoot dossier absolu de la corbeille
     * @param string $webPath chemin web du stockage, sans hôte (/assets/natheotheque)
     * @param string $baseUrl préfixe des URL (option OS_MEDIA_URL), vide pour des URL sans hôte
     */
    public function __construct(
        public string $storageRoot,
        public string $trashRoot,
        public string $webPath,
        public string $baseUrl = '',
    ) {}

    /**
     * URL publique d'un fichier du stockage
     * @param string $key clé du fichier
     * @return string
     */
    public function url(string $key): string
    {
        $base = rtrim(trim($this->baseUrl), '/');
        if ($base !== '' && preg_match('#^https?://#i', $base) !== 1) {
            $base = '/' . implode('/', self::segments($base));
            $base = $base === '/' ? '' : $base;
        }

        return $base . $this->path($key);
    }

    /**
     * Chemin public d'un fichier du stockage, sans hôte
     * @param string $key clé du fichier
     * @return string
     */
    public function path(string $key): string
    {
        return '/' . implode('/', [...self::segments($this->webPath), ...self::segments($key)]);
    }

    /**
     * Clé de la miniature (ou de la couverture) d'un fichier : même clé, extension webp
     * @param string $key
     * @return string
     */
    public static function thumbnailKey(string $key): string
    {
        $key = implode('/', self::segments($key));
        $dot = strrpos($key, '.');
        $slash = strrpos($key, '/');
        if ($dot !== false && ($slash === false || $dot > $slash)) {
            $key = substr($key, 0, $dot);
        }

        return MediaFolderPath::STORAGE_THUMBNAILS->value . '/' . $key . '.webp';
    }

    /**
     * Segments non vides d'un chemin, sans « . » ni « .. »
     * @param string $path
     * @return string[]
     */
    public static function segments(string $path): array
    {
        return array_values(
            array_filter(
                explode('/', str_replace('\\', '/', $path)),
                fn(string $segment) => $segment !== '' && $segment !== '.' && $segment !== '..',
            ),
        );
    }
}
