<?php
/**
 * Permet de merger les données venant d'un tableau à un objet Comment
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Content\Comment;

use App\Entity\Admin\Content\Comment\Comment;

class CommentPopulate
{
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
        $this->comment = $this->mergeData($this->comment, $this->populate, [
            'id',
            'page',
            'userModeration',
            'createdAt',
            'updateAt',
        ]);
        return $this;
    }

    /**
     * Merge des données de $populate dans $object sans prendre en compte $exclude
     * @param mixed $object
     * @param array $populate
     * @param array $exclude
     * @return mixed
     */
    private function mergeData(mixed $object, array $populate, array $exclude = []): mixed
    {
        foreach ($populate as $key => $value) {
            if (in_array($key, $exclude)) {
                continue;
            }
            $func = 'set' . ucfirst($key);
            $object->$func($value);
        }
        return $object;
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
