<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Activation ou désactivation d'un module du back-office ou d'une opération d'API
 */

namespace App\Api\V2\Input\Admin\System;

use Symfony\Component\Validator\Constraints as Assert;

final class UpdateActivationInput
{
    #[Assert\NotNull]
    public ?bool $disabled = null;
}
