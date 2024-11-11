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
            message: 'The username field must be of type string'
        )]
        #[Assert\NotBlank(message: 'Username field cannot be empty')]
        private string $username,

        #[Assert\Type(
            type : 'string',
            message: 'The password field must be of type string'
        )]
        #[Assert\NotBlank(message: 'Password field cannot be empty')]
        private string $password,
    )
    {
    }

    /**
     * Username
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Password
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }


}
