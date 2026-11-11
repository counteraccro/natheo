<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Identifiants d'un utilisateur du back-office dans l'API v2
 */

namespace App\Api\V2\Input\System;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Validator\Constraints as Assert;

final class AuthenticateUserInput
{
    #[ApiProperty(required: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    public string $email = '';

    #[ApiProperty(required: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 4096)]
    public string $password = '';
}
