<?php

declare(strict_types=1);
/**
 * Permet de merger les données venant d'un tableau à un objet Comment
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Content\Comment;

use App\Entity\Admin\Content\Comment\Comment;
use App\Entity\Trait\PopulateTrait;

class CommentPopulate
{
    use PopulateTrait;

    /**
     * Champs modifiables de Comment
     * @var string[]
     */
    private const array FIELDS_COMMENT = ['comment', 'status', 'moderationComment'];

    /**
     * @var Comment
     */
    private Comment $comment;

    /**
     * @var array
     */
    private array $populate;

    /**
     * @param Comment $comment
     * @param array $populate
     */
    public function __construct(Comment $comment, array $populate)
    {
        $this->comment = $comment;
        $this->populate = $populate;
    }

    /**
     * Met les données de $populate dans Comment
     * @return $this
     */
    public function populate(): static
    {
        $this->comment = $this->mergeData($this->comment, $this->populate, self::FIELDS_COMMENT);
        return $this;
    }

    /**
     * Retourne un commentaire
     * @return Comment
     */
    public function getComment(): Comment
    {
        return $this->comment;
    }
}
