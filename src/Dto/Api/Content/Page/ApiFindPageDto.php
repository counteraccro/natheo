<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Object de Transfère de Données pour la recherche de menu via API
 */
namespace App\Dto\Api\Content\Page;

use App\Dto\Api\AppApiDto;
use Symfony\Component\Validator\Constraints as Assert;

class ApiFindPageDto extends AppApiDto
{
    public function __construct(

        /**
         * slug de la page
         * @var string
         */
        #[Assert\Type(type: 'string', message: 'The pageSlug parameter must be a string')]
        #[Assert\NotNull(message: 'The pageSlug parameter cannot be empty')]
        private readonly string $slug,

        /**
         * Locale du menu
         * @var string
         */
        #[Assert\Type(type: 'string', message: 'The locale parameter must be a string')]
        #[Assert\Choice(choices: self::LOCALES, message: 'Choose a locale between en or es or fr')]
        private readonly string $locale,

        /**
         * Token
         * @var string
         */
        #[Assert\Type(type: 'string', message: 'The user_token parameter must be a string')]
        private readonly string $userToken,
    ) {}

    /**
     * Slug
     * @return string
     */
    public function getSlug(): string {
        return $this->slug;
    }

    /**
     * Locale
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * UserToken
     * @return string
     */
    public function getUserToken(): string
    {
        return $this->userToken;
    }
}
