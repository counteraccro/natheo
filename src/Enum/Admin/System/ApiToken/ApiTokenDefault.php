<?php

declare(strict_types=1);
/**
 * Tokens API par défaut, créés par les fixtures
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\System\ApiToken;

enum ApiTokenDefault: string
{
    case READ = 'read.CZjfAZu6FatHfrCU8MaCudqc.GfmytciCqV8P236QSu3jJizG.EfgV96RRTSTxeqVBDHTxX2yh.9xicEZXkXzx7hL85eUZ8YrEJ';

    case WRITE = 'write.CZjfAZu6FatHfrCU8MaCudqc.GfmytciCqV8P236QSu3jJizG.EfgV96RRTSTxeqVBDHTxX2yh.9xicEZXkXzx7hL85eUZ8YrEJ';

    case ADMIN = 'admin.CZjfAZu6FatHfrCU8MaCudqc.GfmytciCqV8P236QSu3jJizG.EfgV96RRTSTxeqVBDHTxX2yh.9xicEZXkXzx7hL85eUZ8YrEJ';
}
