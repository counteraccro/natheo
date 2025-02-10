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
            'legend_search' => $this->translator->trans('comment.moderation.legend.search', domain: 'comment'),
            'status_label' => $this->translator->trans('comment.moderation.status.label', domain: 'comment'),
            'pages_label' => $this->translator->trans('comment.moderation.pages.label', domain: 'comment'),
            'pages_default' => $this->translator->trans('comment.moderation.pages.default', domain: 'comment'),
            'status_default' => $this->translator->trans('comment.moderation.status.default', domain: 'comment'),
            'selection_title' => $this->translator->trans('comment.moderation.selection.title', domain: 'comment'),
            'comment_id' => $this->translator->trans('comment.moderation.comment.id', domain: 'comment'),
            'comment_date' => $this->translator->trans('comment.moderation.comment.date', domain: 'comment'),
            'comment_update' => $this->translator->trans('comment.moderation.comment.update', domain: 'comment'),
            'comment_author' => $this->translator->trans('comment.moderation.comment.author', domain: 'comment'),
            'comment_comment' => $this->translator->trans('comment.moderation.comment.comment', domain: 'comment'),
            'comment_page' => $this->translator->trans('comment.moderation.comment.page', domain: 'comment'),
            'comment_info' => $this->translator->trans('comment.moderation.comment.info', domain: 'comment'),
            'comment_ip' => $this->translator->trans('comment.moderation.comment.ip', domain: 'comment'),
            'comment_user_agent' => $this->translator->trans('comment.moderation.comment.userAgent', domain: 'comment'),
            'comment_moderator' => $this->translator->trans('comment.moderation.comment.moderator', domain: 'comment'),

            'paginate' => $this->getTranslatePaginate()

        ];
    }

    /**
     * Retourne les traductions pour le grid paginate
     * @return array
     */
    private function getTranslatePaginate(): array
    {
        return [
            'page' => $this->translator->trans('comment.moderation.paginate.page', domain: 'comment'),
            'on' => $this->translator->trans('comment.moderation.paginate.on', domain: 'comment'),
            'row' => $this->translator->trans('comment.moderation.paginate.row', domain: 'comment')
        ];
    }
}
