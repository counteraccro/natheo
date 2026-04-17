<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 *  Jeu de donnée de commentaire
 */

namespace App\Tests\Helper\Fixtures\Content;

use App\Entity\Admin\Content\Comment\Comment;
use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\System\User;
use App\Enum\Admin\Comment\Status;
use App\Tests\Helper\FakerTrait;

trait CommentFixturesTrait
{
    use FakerTrait;

    public function createComment(
        ?Page $page = null,
        ?User $userModeration = null,
        array $customData = [],
        bool $persist = true,
    ): Comment {
        if ($page === null) {
            $page = $this->createPage();
            foreach ($this->locales as $locale) {
                $this->createPageTranslation($page, ['locale' => $locale]);
            }
        }

        $data = [
            'author' => self::getFaker()->name(),
            'email' => self::getFaker()->email(),
            'comment' => self::getFaker()->text(),
            'status' => Status::WAIT_VALIDATION->value,
            'disabled' => false,
            'moderationComment' => '',
            'ip' => self::getFaker()->ipv4(),
            'userAgent' => self::getFaker()->userAgent(),
            'page' => $page,
            'userModeration' => $userModeration,
        ];

        $comment = $this->initEntity(Comment::class, array_merge($data, $customData));
        $page->addComment($comment);

        if ($persist) {
            $this->persistAndFlush($comment);
        }
        return $comment;
    }
}
