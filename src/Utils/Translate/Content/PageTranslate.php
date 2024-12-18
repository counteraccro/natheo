<?php
/**
 * Class pour la génération des traductions pour les scripts vue pour Page
 * @author Gourdon Aymeric
 * @version 1.2
 */

namespace App\Utils\Translate\Content;

use App\Utils\Translate\AppTranslate;
use App\Utils\Translate\MarkdownEditorTranslate;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Contracts\Translation\TranslatorInterface;

class PageTranslate extends AppTranslate
{
    private MarkdownEditorTranslate $markdownEditorTranslate;

    public function __construct(#[AutowireLocator([
        'translator' => TranslatorInterface::class,
        'markdownEditorTranslate' => MarkdownEditorTranslate::class
    ])] private readonly ContainerInterface $handlers)
    {
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
            'onglet_content' => $this->translator->trans('page.onglet.content', domain: 'page'),
            'onglet_seo' => $this->translator->trans('page.onglet.seo', domain: 'page'),
            'onglet_tags' => $this->translator->trans('page.onglet.tag', domain: 'page'),
            'onglet_history' => $this->translator->trans('page.onglet.history', domain: 'page'),
            'onglet_save' => $this->translator->trans('page.onglet.save', domain: 'page'),
            'loading' => $this->translator->trans('page.loading', domain: 'page'),
            'msg_auto_save_success' => $this->translator->trans('page.msg.auto_save.success', domain: 'page'),
            'tag_title' => $this->translator->trans('page.onglet.tag.title', domain: 'page'),
            'tag_sub_title' => $this->translator->trans('page.onglet.tag.sub_title', domain: 'page'),
            'msg_add_tag_success' => $this->translator->trans('page.onglet.tag.msg.add_tag_success', domain: 'page'),
            'msg_del_tag_success' => $this->translator->trans('page.onglet.tag.msg.del_tag_success', domain: 'page'),
            'msg_remove_content_success' => $this->translator->trans('page.msg.remove_content_success', domain: 'page'),
            'msg_add_content_success' => $this->translator->trans('page.msg.add_content_success', domain: 'page'),
            'msg_titre_restore_history' => $this->translator->trans('page.msg.titre.restore.history', domain: 'page'),
            'msg_btn_restore_history' => $this->translator->trans('page.msg.btn.restore.history', domain: 'page'),
            'msg_btn_cancel_restore_history' => $this->translator->trans(
                'page.msg.btn.cancel.restore.history', domain: 'page'),
            'msg_error_url_no_unique' => $this->translator->trans('page.msg.error_url_no_unique', domain: 'page'),
            'toast_title_success' => $this->translator->trans('page.toast.title.success', domain: 'page'),
            'toast_title_error' => $this->translator->trans('page.toast.title.error', domain: 'page'),
            'toast_title_auto_save' => $this->translator->trans('page.toast.title.auto_save', domain: 'page'),
            'toast_time' => $this->translator->trans('page.toast.time', domain: 'page'),
            'page_content_form' => $this->getTranslatePageContentForm(),
            'page_content' => $this->getTranslatePageContent(),
            'page_history' => $this->getTranslatePageHistory(),
            'page_save' => $this->getTranslatePageSave(),
            'auto_complete' => $this->getTranslateAutoComplete(),
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
            'input_titre_label' =>
                $this->translator->trans('page.page_content_form.input.titre.label', domain: 'page'),
            'input_titre_info' =>
                $this->translator->trans('page.page_content_form.input.titre.info', domain: 'page'),
            'list_render_label' => $this->translator->trans('page.page_save.list_render_label', domain: 'page'),
            'list_render_help' => $this->translator->trans('page.page_save.list_render_help', domain: 'page'),
            'list_categories_label' => $this->translator->trans('page.page_content.list_categories_label', domain: 'page'),
            'list_categories_help' => $this->translator->trans('page.page_content.list_categories_help', domain: 'page'),
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
            'page_content_block' => [
                'markdown' => $this->markdownEditorTranslate->getTranslate(),
                'btn_new_content' =>
                    $this->translator->trans('page.page_content_block.btn.new_content', domain: 'page'),
                'btn_delete_content' =>
                    $this->translator->trans('page.page_content_block.btn.delete_content', domain: 'page'),
                'btn_change_content' =>
                    $this->translator->trans('page.page_content_block.btn.change_content', domain: 'page'),
                'btn_move_content' =>
                    $this->translator->trans('page.page_content_block.btn.move_content', domain: 'page'),
                'modale_remove_title' =>
                    $this->translator->trans('page.page_content_block.modale.remove.title', domain: 'page'),
                'modale_remove_body' =>
                    $this->translator->trans('page.page_content_block.modale.remove.body', domain: 'page'),
                'modale_remove_body_2' =>
                    $this->translator->trans('page.page_content_block.modale.remove.body.2', domain: 'page'),
                'modale_remove_btn_confirm' =>
                    $this->translator->trans('page.page_content_block.modale.remove.btn.confirm', domain: 'page'),
                'modale_remove_btn_cancel' =>
                    $this->translator->trans('page.page_content_block.modale.remove.btn.cancel', domain: 'page'),
                'modale_new_title' =>
                    $this->translator->trans('page.page_content_block.modale.new.title', domain: 'page'),
                'modale_new_btn_cancel' =>
                    $this->translator->trans('page.page_content_block.modale.new.btn.cancel', domain: 'page'),
                'modale_new_btn_new' =>
                    $this->translator->trans('page.page_content_block.modale.new.btn.new', domain: 'page'),
                'modale_new_choice_label' =>
                    $this->translator->trans('page.page_content_block.modale.new.choice_label', domain: 'page'),
                'modale_new_choice_info' =>
                    $this->translator->trans('page.page_content_block.modale.new.choice_info', domain: 'page'),
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
            'list_menu_label' => $this->translator->trans('page.page_save.list_menu_label', domain: 'page'),
            'list_menu_help' => $this->translator->trans('page.page_save.list_menu_help', domain: 'page'),
            'list_menu_empty' => $this->translator->trans('page.page_save.list_menu_empty', domain: 'page'),
            'list_menu_disabled' => $this->translator->trans('page.page_save.list_menu_disabled', domain: 'page'),
            'list_landing_page_label' => $this->translator->trans('page.page_save.list_landing_page_label', domain: 'page'),
            'select_page_landing_page' => $this->translator->trans('page.page_save.select_page_landing_page', domain: 'page'),
            'select_page_normal_page' => $this->translator->trans('page.page_save.select_page_normal_page', domain: 'page'),
            'list_landing_page_help' => $this->translator->trans('page.page_save.list_landing_page_help', domain: 'page'),
            'btn_save' => $this->translator->trans('page.page_save.btn.save', domain: 'page'),
            'btn_see_ext' => $this->translator->trans('page.page_save.btn.see_ext', domain: 'page'),
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
            'auto_complete_placeholder' =>
                $this->translator->trans('page.tag.auto_complete.placeholder', domain: 'page'),
            'auto_complete_help' => $this->translator->trans('page.tag.auto_complete.help', domain: 'page'),
            'auto_complete_btn' => $this->translator->trans('page.tag.auto_complete.btn', domain: 'page'),
            'auto_complete_btn_loading' =>
                $this->translator->trans('page.tag.auto_complete.btn.loading', domain: 'page'),
            'auto_complete_empty_result' =>
                $this->translator->trans('page.tag.auto_complete.empty.result', domain: 'page')
        ];
    }

    /**
     * Retourne les traductions pour le preview
     * @return array[]
     */
    public function getTranslatePreview(): array
    {
        return [
            'preview_content' => $this->getTranslatePreviewContent()
        ];
    }

    /**
     * Traduction du bloc content
     * @return array
     */
    private function getTranslatePreviewContent(): array {
        return [
            'loading_text' => $this->translator->trans('page.preview.content.loading_text', domain: 'page'),
            'loading_faq' => $this->translator->trans('page.preview.content.loading_faq', domain: 'page'),
            'loading_listing' => $this->translator->trans('page.preview.content.loading_listing', domain: 'page'),
        ];
    }
}
