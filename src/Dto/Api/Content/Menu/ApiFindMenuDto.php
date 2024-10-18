<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Object de Transfère de Données pour la recherche de menu via API
 */
namespace App\Dto\Api\Content\Menu;

use App\Utils\Content\Menu\MenuConst;
use Symfony\Component\Validator\Constraints as Assert;

class ApiFindMenuDto
{
    /**
     * Tableau de positions
     */
    private const MENU_POSITIONS = [
        MenuConst::POSITION_HEADER,
        MenuConst::POSITION_RIGHT,
        MenuConst::POSITION_FOOTER,
        MenuConst::POSITION_LEFT
    ];

    /**
     * Locale prise en charge
     */
    private const MENU_LOCALES = [
        'fr', 'es', 'en'
    ];

    /**
     * @param int $id
     * @param string $pageSlug
     * @param int $position
     * @param string $locale
     * @param string $userToken
     */
    public function __construct(

        /**
         * Id du menu
         * @var int
         */
        #[Assert\Type(type: 'integer', message: 'The id parameter must be a integer')]
        private readonly int $id,

        /**
         * Url de la page ou le menu est associé
         * @var string
         */
        #[Assert\Type(type: 'string', message: 'The pageSlug parameter must be a string')]
        #[Assert\NotNull(message: 'The pageSlug parameter cannot be empty')]
        private readonly string $pageSlug,

        /**
         * Position du menu
         * @var int
         */
        #[Assert\Type(type: 'integer', message: 'The position parameter must be a integer')]
        #[Assert\Choice(choices: self::MENU_POSITIONS, message: 'Choose a position between 1 (top), 2 (right), 3 (bottom), 4 (left)')]
        private readonly int $position,

        /**
         * Locale du menu
         * @var string
         */
        #[Assert\Type(type: 'string', message: 'The locale parameter must be a string')]
        #[Assert\Choice(choices: self::MENU_LOCALES, message: 'Choose a locale between en or es or fr')]
        private readonly string $locale,

        #[Assert\Type(type: 'string', message: 'The user_token parameter must be a string')]
        private readonly string $userToken,
    )
    {

    }

    /**
     * Id
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * PageSlug
     * @return string
     */
    public function getPageSlug(): string
    {
        return $this->pageSlug;
    }

    /**
     * Position
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
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
