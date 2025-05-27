<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * DTO
 */

namespace App\Dto\Api\Content\Comment;

use App\Dto\Api\AppApiDto;
use Symfony\Component\Validator\Constraints as Assert;

class ApiCommentByPageDto extends AppApiDto
{
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
         * Locale de la page
         * @var string
         */
        #[Assert\Type(type: 'string', message: 'The locale parameter must be a string')]
        #[Assert\Choice(choices: self::LOCALES, message: 'Choose a locale between en or es or fr')]
        private readonly string $locale,

        /**
         * Numéro de la page (pagination des elements de la page)
         * @var int
         */
        #[Assert\Type(type: 'integer', message: 'The page parameter must be a integer')]
        private readonly int $page,

        /**
         * Nombre maximum de lignes dans un élement de page
         * @var int
         */
        #[Assert\Type(type: 'integer', message: 'The limit parameter must be a integer')]
        private readonly int $limit,

        /**
         * Token
         * @var string
         */
        #[Assert\Type(type: 'string', message: 'The user_token parameter must be a string')]
        private readonly string $userToken,
    ){}

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
     * Limit
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * Page (Pagination)
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
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
     * User token
     * @return string
     */
    public function getUserToken(): string
    {
        return $this->userToken;
    }
}
