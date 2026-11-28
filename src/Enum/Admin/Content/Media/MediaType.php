<?php

declare(strict_types=1);
/**
 * Types de média
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\Content\Media;

use App\Enum\Trait\EnumSlugTrait;

enum MediaType: string
{
    use EnumSlugTrait;

    case FILE = 'file';

    case IMG = 'img';

    case VIDEO = 'video';

    /**
     * Type déduit du type MIME réel du fichier
     * @param string $mimeType
     * @return self
     */
    public static function fromMimeType(string $mimeType): self
    {
        return match (true) {
            str_starts_with($mimeType, 'image/') => self::IMG,
            str_starts_with($mimeType, 'video/') => self::VIDEO,
            default => self::FILE,
        };
    }
}
