<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Dto pour l'ajout d'un commentaire
 */

namespace App\Dto\Api\Content\Comment;

use App\Dto\Api\AppApiDto;
use Symfony\Component\Validator\Constraints as Assert;

class ApiAddCommentDto extends AppApiDto
{
    public function __construct(
        /**
         * Id de la page
         * @var int
         */
        #[Assert\Type(type: 'integer', message: 'The idPage parameter must be a integer')]
        private readonly int $idPage,

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
         * author
         * @var string
         */
        #[Assert\Type(type: 'string', message: 'The author parameter must be a string')]
        #[Assert\NotNull(message: 'The author parameter cannot be empty')]
        #[Assert\NotBlank(message: 'The author parameter cannot be empty')]
        private readonly string $author,

        /**
         * author
         * @var string
         */
        #[Assert\Email(message: 'The email parameter must be a valid email')]
        private readonly string $email,

        /**
         * author
         * @var string
         */
        #[Assert\Type(type: 'string', message: 'The comment parameter must be a string')]
        #[Assert\NotNull(message: 'The comment parameter cannot be empty')]
        #[Assert\NotBlank(message: 'The comment parameter cannot be empty')]
        private readonly string $comment,

        /**
         * ip
         * @var string
         */
        #[Assert\Ip(message: 'The ip parameter must be a valid ip')]
        private readonly string $ip,

        /**
         * user agent
         * @var string
         */
        #[Assert\Type(type: 'string', message: 'The userAgent parameter must be a valid ip')]
        private readonly string $userAgent,
    ) {

}

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @return int
     */
    public function getIdPage(): int
    {
        return $this->idPage;
    }

    /**
     * @return string
     */
    public function getPageSlug(): string
    {
        return $this->pageSlug;
    }

    /**
     * @return string
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @return string
     */
    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

}
