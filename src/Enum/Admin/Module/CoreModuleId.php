<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Identifiants des modules du back-office fournis par le cœur
 */

namespace App\Enum\Admin\Module;

enum CoreModuleId: string
{
    case DASHBOARD = 'dashboard';

    case PAGE = 'page';

    case MENU = 'menu';

    case FAQ = 'faq';

    case TAG = 'tag';

    case COMMENT = 'comment';

    case MEDIA = 'media';

    case USER = 'user';

    case API_TOKEN = 'api_token';

    case API_DOC = 'api_doc';

    case OPTION_SYSTEM = 'option_system';

    case ADVANCED_OPTION = 'advanced_option';

    case TRANSLATION = 'translation';

    case MAIL = 'mail';

    case LOG = 'log';

    case INFO = 'info';

    case DATABASE_MANAGER = 'database_manager';

    case SQL_MANAGER = 'sql_manager';
}
