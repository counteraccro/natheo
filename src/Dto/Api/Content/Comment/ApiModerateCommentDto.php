<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 *
 */

namespace App\Dto\Api\Content\Comment;

use App\Dto\Api\AppApiDto;
use Symfony\Component\Validator\Constraints as Assert;

class ApiModerateCommentDto extends AppApiDto
{
    public function __construct(
        /**
         * status
         * @var string
         */
        #[Assert\Type(type: 'string', message: 'The status parameter must be a string')]
        #[Assert\NotNull(message: 'The status parameter cannot be empty')]
        #[Assert\NotBlank(message: 'The status parameter cannot be empty')]
        private readonly string $status,

        /**
         * moderation comment
         * @var string
         */
        #[Assert\Type(type: 'string', message: 'The moderation_comment parameter must be a string')]
        private readonly string $moderationComment,

        /**
         * Token
         * @var string
         */
        #[Assert\Type(type: 'string', message: 'The user_token parameter must be a string')]
        private readonly string $userToken,
    ){}

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getModerationComment(): string
    {
        return $this->moderationComment;
    }

    /**
     * @return string
     */
    public function getUserToken(): string
    {
        return $this->userToken;
    }
}
