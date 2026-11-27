<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Traductions d'une création ou d'une modification de FAQ, de catégorie ou de question, catégorie d'une question
 * prise dans sa FAQ
 */

namespace App\Api\V2\Validator\Admin\Content;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class ValidFaqInput extends Constraint
{
    /**
     * @return string
     */
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
