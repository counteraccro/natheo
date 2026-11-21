<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Couleur et libellés d'une création ou d'une modification de tag, libellé unique par langue sans tenir compte
 * de la casse
 */

namespace App\Api\V2\Validator\Admin\Content;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class ValidTagInput extends Constraint
{
    /**
     * @return string
     */
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
