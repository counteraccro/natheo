<?php

declare(strict_types=1);
/**
 *
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\Content\Page;

enum PageStatistics: string
{
    case NB_VISITEUR = 'PAGE_NB_VISITEUR';

    case NB_READ = 'PAGE_NB_READ';
}
