<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Nature d'une opération d'API du back-office, lue dans le suffixe de son nom
 */

namespace App\Enum\Admin\Module;

use App\Enum\Trait\EnumSlugTrait;

enum OperationKind: string
{
    use EnumSlugTrait;

    case GET_COLLECTION = 'get_collection';

    case GET = 'get';

    case POST = 'post';

    case PUT = 'put';

    case PATCH = 'patch';

    case DELETE = 'delete';
}
