<?php

namespace App\Utils\Content\Comment;

class CommentConst
{
    /**
     * En attente de validation
     * @type int
     */
    const WAIT_VALIDATION = 1;

    /**
     * Validé
     * @type int
     */
    const VALIDATE = 2;

    /**
     * En attente de modération
     */
    const WAIT_MODERATION = 3;

    /**
     * Modéré
     */
    const MODERATE = 4;
}
