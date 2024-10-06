<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Object de Transfère de Données pour l'authentification d'un user via API
 */
namespace App\Dto\Api\Authentication;

use Symfony\Component\Validator\Constraints as Assert;

readonly class ApiAuthUserDto
{
    public function __construct(

        #[Assert\Type(
            type : 'string',
            message: 'Le champ username doit être du type string'
        )]
        #[Assert\NotBlank(message: 'Le champ username ne peut pas être vide')]
        public string $username,

        #[Assert\Type(
            type : 'string',
            message: 'Le champ password doit être du type string'
        )]
        #[Assert\NotBlank(message: 'Le champ password ne peut pas être vide')]
        public string $password,
    )
    {
    }
}
