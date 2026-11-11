<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Chemins publics des avatars des utilisateurs
 */

namespace App\Service\Global\User;

class AvatarUrl
{
    /**
     * Chemin public du dossier des avatars, terminé par un slash
     * @param string $avatarPath paramètre app.path.avatar
     * @param string $basePath base path de la requête
     * @return string
     */
    public static function directory(string $avatarPath, string $basePath = ''): string
    {
        return $basePath . '/' . trim($avatarPath, '/') . '/';
    }

    /**
     * Chemin public d'un avatar, null sans avatar
     * @param string|null $avatar nom du fichier
     * @param string $avatarPath paramètre app.path.avatar
     * @param string $basePath base path de la requête
     * @return string|null
     */
    public static function file(?string $avatar, string $avatarPath, string $basePath = ''): ?string
    {
        return $avatar === null || $avatar === '' ? null : self::directory($avatarPath, $basePath) . $avatar;
    }
}
