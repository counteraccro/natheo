<?php

declare(strict_types=1);
/**
 * Clés des emails
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\System\Mail;

enum MailKey: string
{
    case CHANGE_PASSWORD = 'MAIL_CHANGE_PASSWORD';

    case RESET_PASSWORD = 'MAIL_RESET_PASSWORD';

    case ACCOUNT_ADM_DISABLE = 'MAIL_ACCOUNT_ADM_DISABLE';

    case ACCOUNT_ADM_ENABLE = 'MAIL_ACCOUNT_ADM_ENABLE';

    case CREATE_ACCOUNT_ADM = 'MAIL_CREATE_ACCOUNT_ADM';

    case SELF_DISABLED_ACCOUNT = 'MAIL_SELF_DISABLED_ACCOUNT';

    case SELF_DELETE_ACCOUNT = 'MAIL_SELF_DELETE_ACCOUNT';

    case SELF_ANONYMOUS_ACCOUNT = 'MAIL_SELF_ANONYMOUS_ACCOUNT';
}
