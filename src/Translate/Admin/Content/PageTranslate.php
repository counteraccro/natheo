<?php

declare(strict_types=1);
/**
 * Class pour la génération des traductions pour les scripts vue pour Page
 * @author Gourdon Aymeric
 * @version 1.2
 */

namespace App\Translate\Admin\Content;

use App\Translate\AppTranslate;
use App\Translate\Admin\Global\MarkdownEditorTranslate;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Contracts\Translation\TranslatorInterface;

class PageTranslate extends AppTranslate
{
    private MarkdownEditorTranslate $markdownEditorTranslate;

    public function __construct(
        #[
            AutowireLocator([
                'translator' => TranslatorInterface::class,
                'markdownEditorTranslate' => MarkdownEditorTranslate::class,
            ]),
        ]
        private readonly ContainerInterface $handlers,
    ) {
        $this->markdownEditorTranslate = $this->handlers->get('markdownEditorTranslate');
        parent::__construct($this->handlers);
    }

    /**
     * Génération du tableau de translate pour le script vue de Page
     * @return array
     */
    public function getTranslate(): array
    {
        return [
            'select_locale' => $this->translator->trans('page.select.locale', domain: 'page'),
            'onglet_content' => $this->translator->trans('page.tab.content', domain: 'page'),
            'onglet_seo' => $this->translator->trans('page.tab.seo', domain: 'page'),
            'onglet_tags' => $this->translator->trans('page.tab.tag', domain: 'page'),
            'onglet_comments' => $this->translator->trans('page.tab.comments', domain: 'page'),
            'onglet_menu' => $this->translator->trans('page.tab.menu', domain: 'page'),
            'onglet_history' => $this->translator->trans('page.tab.history', domain: 'page'),
            'onglet_information' => $this->translator->trans('page.tab.information', domain: 'page'),
            'loading' => $this->translator->trans('page.edit.loading', domain: 'page'),
            'tag_title' => $this->translator->trans('page.tab.tag_title', domain: 'page'),
            'tag_sub_title' => $this->translator->trans('page.tab.tag_sub_title', domain: 'page'),
            'msg_titre_restore_history' => $this->translator->trans(
                'page.message.title.restore.history',
                domain: 'page',
            ),
            'msg_btn_restore_history' => $this->translator->trans(
                'page.message.button.restore.history',
                domain: 'page',
            ),
            'msg_btn_cancel_restore_history' => $this->translator->trans(
                'page.message.button.cancel.restore.history',
                domain: 'page',
            ),
            'msg_error_url_no_unique' => $this->translator->trans('page.message.error_url_no_unique', domain: 'page'),
            'page_no_exist_title' => $this->translator->trans('page.page.no.exist.title', domain: 'page'),
            'page_no_exist_text' => $this->translator->trans('page.page.no.exist.text', domain: 'page'),
            'btn_back' => $this->translator->trans('page.button.back', domain: 'page'),
            'btn_new' => $this->translator->trans('page.button.new', domain: 'page'),
            'auto_save_success' => $this->translator->trans('page.message.auto_save.success', domain: 'page'),
            'auto_save_error' => $this->translator->trans('page.message.auto_save.error', domain: 'page'),
            'page_content_form' => $this->getTranslatePageContentForm(),
            'page_content' => $this->getTranslatePageContent(),
            'page_history' => $this->getTranslatePageHistory(),
            'page_save' => $this->getTranslatePageSave(),
            'auto_complete' => $this->getTranslateAutoComplete(),
            'page_comment' => $this->getTranslatePageComment(),
            'page_seo' => $this->getTranslatePageSeo(),
            'page_menu' => $this->getTranslatePageMenu(),
            'page_draft' => $this->getTranslatePageDraft(),
            'page_validation' => $this->getTranslatePageValidation(),
        ];
    }

    /**
     * Traductions du brouillon des pages publiées et des pages archivées
     * @return array
     */
    private function getTranslatePageDraft(): array
    {
        $keys = [
            'badge',
            'banner',
            'published_info',
            'archived_banner',
            'button_save',
            'button_publish',
            'button_discard',
            'publish_confirm_title',
            'publish_confirm_text',
            'publish_confirm_cancel',
            'publish_confirm_ok',
            'discard_confirm_title',
            'discard_confirm_text',
            'discard_confirm_cancel',
            'discard_confirm_ok',
        ];

        $translate = [];
        foreach ($keys as $key) {
            $translate[$key] = $this->translator->trans('page.draft.' . $key, domain: 'page');
        }
        return $translate;
    }

    /**
     * Traductions de la validation des pages
     * @return array
     */
    private function getTranslatePageValidation(): array
    {
        $keys = [
            'button_submit',
            'button_approve',
            'button_refuse',
            'button_withdraw',
            'submit_confirm_title',
            'submit_confirm_text',
            'submit_confirm_cancel',
            'submit_confirm_ok',
            'approve_confirm_title',
            'approve_confirm_text',
            'approve_confirm_cancel',
            'approve_confirm_ok',
            'refuse_confirm_title',
            'refuse_confirm_text',
            'refuse_reason_label',
            'refuse_reason_help',
            'refuse_confirm_cancel',
            'refuse_confirm_ok',
        ];

        $translate = [];
        foreach ($keys as $key) {
            $translate[$key] = $this->translator->trans('page.validation.' . $key, domain: 'page');
        }
        return $translate;
    }

    private function getTranslatePageSEO(): array
    {
        return [
            'title' => $this->translator->trans('page.page_seo.title', domain: 'page'),
            'input_meta_description_label' => $this->translator->trans(
                'page.page_seo.input.meta_description.label',
                domain: 'page',
            ),
            'input_meta_description_help' => $this->translator->trans(
                'page.page_seo.input.meta_description.help',
                domain: 'page',
            ),
            'input_meta_keywords_label' => $this->translator->trans(
                'page.page_seo.input.meta_keywords.label',
                domain: 'page',
            ),
            'input_meta_keywords_help' => $this->translator->trans(
                'page.page_seo.input.meta_keywords.help',
                domain: 'page',
            ),
            'input_meta_author_label' => $this->translator->trans(
                'page.page_seo.input.meta_author.label',
                domain: 'page',
            ),
            'input_meta_author_help' => $this->translator->trans(
                'page.page_seo.input.meta_author.help',
                domain: 'page',
            ),
            'input_meta_copyright_label' => $this->translator->trans(
                'page.page_seo.input.meta_copyright.label',
                domain: 'page',
            ),
            'input_meta_copyright_help' => $this->translator->trans(
                'page.page_seo.input.meta_copyright.help',
                domain: 'page',
            ),
            'input_meta_description_error' => $this->translator->trans(
                'page.page_seo.input.meta.description.error',
                domain: 'page',
            ),
            'input_meta_keywords_error' => $this->translator->trans(
                'page.page_seo.input.meta.keywords.error',
                domain: 'page',
            ),
            'input_meta_author_error' => $this->translator->trans(
                'page.page_seo.input.meta.author.error',
                domain: 'page',
            ),
            'input_meta_copyright_error' => $this->translator->trans(
                'page.page_seo.input.meta.copyright.error',
                domain: 'page',
            ),
        ];
    }

    /**
     * Traduction de l'onglet Comment
     * @return array
     */
    private function getTranslatePageComment(): array
    {
        return [
            'title' => $this->translator->trans('page.page_comment.title', domain: 'page'),
            'input_open_comment' => $this->translator->trans('page.page_comment.input_open', domain: 'page'),
            'input_status_comment_label' => $this->translator->trans(
                'page.page_comment.input_status_label',
                domain: 'page',
            ),
            'input_status_comment_help' => $this->translator->trans(
                'page.page_comment.input_status_help',
                domain: 'page',
            ),
            'info' => $this->translator->trans('page.page_comment.info', domain: 'page'),
            'comment_open' => $this->translator->trans('page.page_comment.comment.open', domain: 'page'),
            'comment_close' => $this->translator->trans('page.page_comment.comment.close', domain: 'page'),
            'comment_moderate' => $this->translator->trans('page.page_comment.comment.moderate', domain: 'page'),
        ];
    }

    /**
     * Retourne la traduction du bloc page content form
     * @return array
     */
    private function getTranslatePageContentForm(): array
    {
        return [
            'title' => $this->translator->trans('page.page_content_form.title', domain: 'page'),
            'input_url_label' => $this->translator->trans('page.page_content_form.input.url.label', domain: 'page'),
            'input_url_info' => $this->translator->trans('page.page_content_form.input.url.info', domain: 'page'),
            'input_titre_label' => $this->translator->trans('page.page_content_form.input.title.label', domain: 'page'),
            'input_titre_info' => $this->translator->trans('page.page_content_form.input.title.info', domain: 'page'),
            'input_titre_error' => $this->translator->trans('page.page_content_form.input.title.error', domain: 'page'),
            'list_render_label' => $this->translator->trans('page.page_save.list_render_label', domain: 'page'),
            'list_render_help' => $this->translator->trans('page.page_save.list_render_help', domain: 'page'),
            'list_categories_label' => $this->translator->trans(
                'page.page_content.list_categories_label',
                domain: 'page',
            ),
            'list_categories_help' => $this->translator->trans(
                'page.page_content.list_categories_help',
                domain: 'page',
            ),
            'header_img_title' => $this->translator->trans('page.page_content.header_img.title', domain: 'page'),
            'header_img_help' => $this->translator->trans('page.page_content.header_img.help', domain: 'page'),
            'header_img_no_img' => $this->translator->trans('page.page_content.header_img.no.img', domain: 'page'),
            'header_img_remove' => $this->translator->trans('page.page_content.header_img.remove', domain: 'page'),
            'header_img_change' => $this->translator->trans('page.page_content.header_img.change', domain: 'page'),
            'auto_slug_label' => $this->translator->trans('page.page_content.auto_slug.label', domain: 'page'),
        ];
    }

    /**
     * Retourne la traduction du bloc page content
     * @return array
     */
    private function getTranslatePageContent(): array
    {
        return [
            'title' => $this->translator->trans('page.page_content.title', domain: 'page'),
            'warning_empty_content' => $this->translator->trans(
                'page.page_content.warning.empty_content',
                domain: 'page',
            ),
            'page_content_block' => [
                'faq_id' => $this->translator->trans('page.page_content_block.faq_id', domain: 'page'),
                'listing_id' => $this->translator->trans('page.page_content_block.listing_id', domain: 'page'),
                'type_faq' => $this->translator->trans('page.content.type_faq', domain: 'page'),
                'type_listing' => $this->translator->trans('page.content.type_listing', domain: 'page'),
                'markdown' => $this->markdownEditorTranslate->getTranslate(),
                'btn_new_content' => $this->translator->trans(
                    'page.page_content_block.button.new_content',
                    domain: 'page',
                ),
                'btn_delete_content' => $this->translator->trans(
                    'page.page_content_block.button.delete_content',
                    domain: 'page',
                ),
                'modale_remove_title' => $this->translator->trans(
                    'page.page_content_block.modal.remove.title',
                    domain: 'page',
                ),
                'modale_remove_body' => $this->translator->trans(
                    'page.page_content_block.modal.remove.body',
                    domain: 'page',
                ),
                'modale_remove_btn_confirm' => $this->translator->trans(
                    'page.page_content_block.modal.remove.button.confirm',
                    domain: 'page',
                ),
                'modale_remove_btn_cancel' => $this->translator->trans(
                    'page.page_content_block.modal.remove.button.cancel',
                    domain: 'page',
                ),
                'modale_new_title' => $this->translator->trans(
                    'page.page_content_block.modal.new.title',
                    domain: 'page',
                ),
                'modale_new_btn_cancel' => $this->translator->trans(
                    'page.page_content_block.modal.new.button.cancel',
                    domain: 'page',
                ),
                'modale_new_btn_new' => $this->translator->trans(
                    'page.page_content_block.modal.new.button.new',
                    domain: 'page',
                ),
                'modale_new_choice_label' => $this->translator->trans(
                    'page.page_content_block.modal.new.choice_label',
                    domain: 'page',
                ),
                'modale_new_choice_info' => $this->translator->trans(
                    'page.page_content_block.modal.new.choice_info',
                    domain: 'page',
                ),
                'default_text' => $this->translator->trans('page.page_content_block.default_text', domain: 'page'),
                'bloc' => $this->translator->trans('page.page_content_block.block', domain: 'page'),
                'loading' => $this->translator->trans('page.page_content_block.loading', domain: 'page'),
            ],
        ];
    }

    /**
     * Retourne la traduction du bloc page history
     * @return array
     */
    private function getTranslatePageHistory(): array
    {
        return [
            'title' => $this->translator->trans('page.page_history.title', domain: 'page'),
            'description' => $this->translator->trans('page.page_history.description', domain: 'page'),
            'empty' => $this->translator->trans('page.page_history.empty', domain: 'page'),
            'id' => $this->translator->trans('page.page_history.id', domain: 'page'),
            'time' => $this->translator->trans('page.page_history.time', domain: 'page'),
            'user' => $this->translator->trans('page.page_history.user', domain: 'page'),
            'action' => $this->translator->trans('page.page_history.action', domain: 'page'),
            'reload' => $this->translator->trans('page.page_history.reload', domain: 'page'),
            'load_more' => $this->translator->trans('page.page_history.load_more', domain: 'page'),
        ];
    }

    /**
     * Retourne la traduction du bloc page save
     * @return array
     */
    private function getTranslatePageSave(): array
    {
        return [
            'title' => $this->translator->trans('page.page_save.title', domain: 'page'),
            'list_status_label' => $this->translator->trans('page.page_save.list_status_label', domain: 'page'),
            'list_status_help' => $this->translator->trans('page.page_save.list_status_help', domain: 'page'),
            'list_landing_page_label' => $this->translator->trans(
                'page.page_save.list_landing_page_label',
                domain: 'page',
            ),
            'select_page_landing_page' => $this->translator->trans(
                'page.page_save.select_page_landing_page',
                domain: 'page',
            ),
            'select_page_normal_page' => $this->translator->trans(
                'page.page_save.select_page_normal_page',
                domain: 'page',
            ),
            'list_landing_page_help' => $this->translator->trans(
                'page.page_save.list_landing_page_help',
                domain: 'page',
            ),
            'btn_save' => $this->translator->trans('page.page_save.button.save', domain: 'page'),
            'btn_see_ext' => $this->translator->trans('page.page_save.button.see_ext', domain: 'page'),
        ];
    }

    private function getTranslatePageMenu(): array
    {
        return [
            'list_menu_label' => $this->translator->trans('page.page_menu.list_menu_label', domain: 'page'),
            'list_menu_help' => $this->translator->trans('page.page_menu.list_menu_help', domain: 'page'),
            'list_menu_empty' => $this->translator->trans('page.page_menu.list_menu_empty', domain: 'page'),
            'list_menu_disabled' => $this->translator->trans('page.page_menu.list_menu_disabled', domain: 'page'),
            'list_menu_header' => $this->translator->trans('page.page_menu.list_menu.header', domain: 'page'),
            'list_menu_footer' => $this->translator->trans('page.page_menu.list_menu.footer', domain: 'page'),
            'list_menu_left' => $this->translator->trans('page.page_menu.list_menu.left', domain: 'page'),
            'list_menu_right' => $this->translator->trans('page.page_menu.list_menu.right', domain: 'page'),
            'list_menu_sub_label' => $this->translator->trans('page.page_menu.list_menu.sub_label', domain: 'page'),
        ];
    }

    /**
     * Retourne la traduction du bloc auto complete
     * @return array
     */
    private function getTranslateAutoComplete(): array
    {
        return [
            'auto_complete_label' => $this->translator->trans('page.tag.auto_complete.label', domain: 'page'),
            'auto_complete_placeholder' => $this->translator->trans(
                'page.tag.auto_complete.placeholder',
                domain: 'page',
            ),
            'auto_complete_help' => $this->translator->trans('page.tag.auto_complete.help', domain: 'page'),
        ];
    }
}
