<?php

declare(strict_types=1);
/**
 * Types de média
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\Content\Media;

enum MediaType: string
{
    case FILE = 'file';

    case IMG = 'img';
}
