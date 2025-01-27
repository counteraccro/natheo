<?php

namespace App\Utils\Translate\Content;

use App\Utils\Translate\AppTranslate;

class CommentTranslate extends AppTranslate
{
    public function getTranslateCommentSee()
    {
        return [
            'loading' => $this->translator->trans('comment.see.loading', domain: 'comment'),
            'author' => $this->translator->trans('comment.see.author', domain: 'comment'),
            'created' => $this->translator->trans('comment.see.created', domain: 'comment'),
            'titleInfo' => $this->translator->trans('comment.see.title.info', domain: 'comment'),
            'ip' => $this->translator->trans('comment.see.ip', domain: 'comment'),
            'userAgent' => $this->translator->trans('comment.see.userAgent', domain: 'comment'),
            'moderationComment' => $this->translator->trans('comment.see.moderation.comment', domain: 'comment'),
        ];
    }
}
