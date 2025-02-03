<?php

namespace App\Utils\Translate\Content;

use App\Utils\Translate\AppTranslate;

class CommentTranslate extends AppTranslate
{
    /**
     * Traduction de la page de modération d'un commentaire
     * @return array
     */
    public function getTranslateCommentSee(): array
    {
        return [
            'loading' => $this->translator->trans('comment.see.loading', domain: 'comment'),
            'author' => $this->translator->trans('comment.see.author', domain: 'comment'),
            'created' => $this->translator->trans('comment.see.created', domain: 'comment'),
            'titleInfo' => $this->translator->trans('comment.see.title.info', domain: 'comment'),
            'ip' => $this->translator->trans('comment.see.ip', domain: 'comment'),
            'userAgent' => $this->translator->trans('comment.see.userAgent', domain: 'comment'),
            'moderationComment' => $this->translator->trans('comment.see.moderation.comment', domain: 'comment'),
            'moderationAuthor' => $this->translator->trans('comment.see.moderation.author', domain: 'comment'),
            'btnEdit' => $this->translator->trans('comment.see.btn.edit', domain: 'comment'),
            'toast_title_success' => $this->translator->trans('comment.see.toast.title.success', domain: 'comment'),
            'toast_title_error' => $this->translator->trans('comment.see.toast.title.error', domain: 'comment'),
            'toast_time' => $this->translator->trans('comment.see.toast.time', domain: 'comment'),
        ];
    }

    /**
     * Traduction de la page de modération des commentaires
     * @return array
     */
    public function getTranslateCommentModeration(): array
    {
        return [
            'loading' => $this->translator->trans('comment.moderation.loading', domain: 'comment'),
            'toast_title_success' => $this->translator->trans('comment.moderation.toast.title.success', domain: 'comment'),
            'toast_title_error' => $this->translator->trans('comment.moderation.toast.title.error', domain: 'comment'),
            'toast_time' => $this->translator->trans('comment.moderation.toast.time', domain: 'comment'),
        ];
    }
}
