<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Object de Transfère de Données pour l'authentification d'un user via API
 */
namespace App\Dto\Api\Authentication;

use Symfony\Component\Validator\Constraints as Assert;

class ApiAuthUserDto
{
    public function __construct(

        #[Assert\Type(
            type: 'integer',
            message: 'The value {{ value }} is not a valid {{ type }}.',
        )]
        #[Assert\NotBlank(message: 'Le champ username ne peut pas être vide')]
        public readonly string $username,

        #[Assert\NotBlank(message: 'Le champ password ne peut pas être vide')]
        public readonly string $password,
    )
    {
    }
}
