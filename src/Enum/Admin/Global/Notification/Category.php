<?php
/**
 * Catégorie de notification
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\Global\Notification;

enum Category: string
{
    case COMMENT = 'comment';

    case ADMIN = 'admin';

    case SQL = 'SQL';
}
