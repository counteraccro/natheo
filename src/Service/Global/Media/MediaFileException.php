<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Fichier refusé ou illisible par le stockage des médias
 */

namespace App\Service\Global\Media;

class MediaFileException extends \RuntimeException
{
    /**
     * Fichier source absent ou illisible
     */
    public const string UNREADABLE = 'unreadable';

    /**
     * Couverture qui n'est pas une image JPEG, PNG ou WebP lisible
     */
    public const string COVER_INVALID = 'cover_invalid';

    /**
     * Couverture plus grande que la taille acceptée
     */
    public const string COVER_TOO_LARGE = 'cover_too_large';

    /**
     * @param string $reason une des constantes de la classe
     */
    public function __construct(public readonly string $reason)
    {
        parent::__construct($reason);
    }
}
