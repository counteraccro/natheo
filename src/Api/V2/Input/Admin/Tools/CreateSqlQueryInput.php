<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Données d'enregistrement d'une requête dans le gestionnaire SQL
 */

namespace App\Api\V2\Input\Admin\Tools;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateSqlQueryInput
{
    /**
     * Nom de la requête, un nom par défaut s'il est absent ou vide
     */
    #[Assert\Length(max: 255)]
    public ?string $name = null;

    #[ApiProperty(required: true)]
    #[Assert\NotBlank]
    public string $query = '';
}
