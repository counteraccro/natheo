<?php
/**
 * Clés de config pour le tableau de notification
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\Global\Notification;

enum KeyConfig: string
{
    case CATEGORY = 'category';
    case LEVEL = 'level';
    case PARAMETERS = 'parameters';
    case TITLE = 'title';
    case CONTENT = 'content';
}
