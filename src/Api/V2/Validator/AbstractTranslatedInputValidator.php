<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Base des validateurs des entrées de l'API, violations écrites avec un message traduisible
 */

namespace App\Api\V2\Validator;

use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\ConstraintValidator;

abstract class AbstractTranslatedInputValidator extends ConstraintValidator
{
    /**
     * Ajoute une violation sur un champ, traduite dans le domaine du message
     * @param TranslatableMessage $message
     * @param string $path
     * @return void
     */
    protected function addViolation(TranslatableMessage $message, string $path): void
    {
        $this->context
            ->buildViolation($message->getMessage(), $message->getParameters())
            ->setTranslationDomain($message->getDomain())
            ->atPath($path)
            ->addViolation();
    }
}
