<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Nouvelle valeur d'une option de l'utilisateur
 */

namespace App\Api\V2\Input\Admin\System;

use App\Api\V2\Validator\Admin\System\ValidUserOptionInput;

#[ValidUserOptionInput]
final class UpdateUserOptionInput
{
    /**
     * Valeur parmi celles de l'option
     */
    public ?string $value = null;
}
