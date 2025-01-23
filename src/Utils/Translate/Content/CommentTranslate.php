<?php

namespace App\Utils\Translate\Content;

use App\Utils\Translate\AppTranslate;

class CommentTranslate extends AppTranslate
{
    public function getTranslateCommentSee()
    {
        return [
            'loading' => $this->translator->trans('comment.see.loading', domain: 'comment'),
        ];
    }
}
