<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Object de Transfère de Données pour la recherche de menu via API
 */
namespace App\Dto\Api\Content\Page;

use App\Dto\Api\AppApiDto;
use App\Utils\Content\Menu\MenuConst;
use Symfony\Component\Validator\Constraints as Assert;

class ApiFindPageDto extends AppApiDto
{
    /**
     * Tableau de positions
     */
    private const MENU_POSITIONS = [
        0,
        MenuConst::POSITION_HEADER,
        MenuConst::POSITION_RIGHT,
        MenuConst::POSITION_FOOTER,
        MenuConst::POSITION_LEFT
    ];

    public function __construct(

        /**
         * slug de la page
         * @var string
         */
        #[Assert\Type(type: 'string', message: 'The pageSlug parameter must be a string')]
        #[Assert\NotNull(message: 'The pageSlug parameter cannot be empty')]
        private readonly string $slug,

        /**
         * Locale de la page
         * @var string
         */
        #[Assert\Type(type: 'string', message: 'The locale parameter must be a string')]
        #[Assert\Choice(choices: self::LOCALES, message: 'Choose a locale between en or es or fr')]
        private readonly string $locale,

        /**
         * Show menu
         * @var bool
         */
        #[Assert\Type(type: 'boolean', message: 'The show_menus parameter must be a boolean')]
        private readonly bool $showMenus,

        /**
         * Show menu
         * @var bool
         */
        #[Assert\Type(type: 'boolean', message: 'The show_tags parameter must be a boolean')]
        private readonly bool $showTags,

        /**
         * Show menu
         * @var bool
         */
        #[Assert\Type(type: 'boolean', message: 'The show_statistiques parameter must be a boolean')]
        private readonly bool $showStatistiques,

        /**
         * Position des menus
         * @var array
         */
        #[Assert\Type(type: 'array', message: 'The show_menus parameter must be a boolean')]
        #[Assert\Choice(choices: self::MENU_POSITIONS, multiple: true, multipleMessage: 'Choose a position between 0, (all), 1 (top), 2 (right), 3 (bottom), 4 (left)')]
        private readonly array $menuPositions,

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
     * show menus
     * @return bool
     */
    public function isShowMenus(): bool
    {
        return $this->showMenus;
    }

    /**
     * show tags
     * @return bool
     */
    public function isShowTags(): bool
    {
        return $this->showTags;
    }

    /**
     * show statistiques
     * @return bool
     */
    public function isShowStatistiques(): bool
    {
        return $this->showStatistiques;
    }



    /**
     * Menu position
     * @return array
     */
    public function getMenuPositions(): array
    {
        return $this->menuPositions;
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
