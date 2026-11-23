<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Nature d'un élément désactivé du back-office : un module entier ou une opération d'API
 */

namespace App\Enum\Admin\Module;

enum ActivationType: string
{
    case MODULE = 'module';

    case OPERATION = 'operation';
}
