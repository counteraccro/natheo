<?php
/**
 * Class pour la génération des traductions pour les scripts vue pour Mediatheque
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Translate\Content;

use App\Utils\Translate\AppTranslate;

class MediaTranslate extends AppTranslate
{
    /**
     * Retourne la traduction de la médiathèque
     * @return array
     */
    public function getTranslate(): array
    {
        return [
            'loading' => $this->translator->trans('media.mediatheque.loading', domain: 'media'),
            'btn_new_folder' => $this->translator->trans('media.mediatheque.btn.new.folder', domain: 'media'),
            'btn_new_media' => $this->translator->trans('media.mediatheque.btn.new.media', domain: 'media'),
            'btn_filtre' => $this->translator->trans('media.mediatheque.btn.filtre', domain: 'media'),
            'filtre_date' => $this->translator->trans('media.mediatheque.filtre.date', domain: 'media'),
            'filtre_nom' => $this->translator->trans('media.mediatheque.filtre.nom', domain: 'media'),
            'filtre_type' => $this->translator->trans('media.mediatheque.filtre.type', domain: 'media'),
            'order_asc' => $this->translator->trans('media.mediatheque.order.asc', domain: 'media'),
            'order_desc' => $this->translator->trans('media.mediatheque.order.desc', domain: 'media'),
            'search_placeholder' => $this->translator->trans('media.mediatheque.search.placeholder', domain: 'media'),
            'media' => $this->getMediaTranslate(),
            'folder' => $this->getFolderTranslate(),
            'info' => $this->getInfoTranslate(),
            'upload' => $this->getUploadTranslate(),
            'edit_media' => $this->getEditMediaTranslate(),
            'move' => $this->getMoveTranslate(),
            'trash' => $this->getTrashTranslate(),
            'remove' => $this->getRemoveTranslate(),
        ];
    }

    /**
     * Retourne les traduction pour l'action remove
     * @return array
     */
    private function getRemoveTranslate(): array
    {
        return [
            'title' => $this->translator->trans('media.mediatheque.remove.title', domain: 'media'),
            'text_folder' => $this->translator->trans('media.mediatheque.remove.text.folder', domain: 'media'),
            'text_media' => $this->translator->trans('media.mediatheque.remove.text.media', domain: 'media'),
            'text_info' => $this->translator->trans('media.mediatheque.remove.text.info', domain: 'media'),
            'text_info_2' => $this->translator->trans('media.mediatheque.remove.text.info2', domain: 'media'),
            'loading' => $this->translator->trans('media.mediatheque.remove.loading', domain: 'media'),
            'btn_confirm' => $this->translator->trans('media.mediatheque.remove.btn.confirm', domain: 'media'),
            'btn_cancel' => $this->translator->trans('media.mediatheque.remove.btn.cancel', domain: 'media'),
            'success' => $this->translator->trans('media.mediatheque.remove.success', domain: 'media'),
        ];
    }

    /**
     * Retourne les traductions pour l'action trash
     * @return array
     */
    private function getTrashTranslate(): array
    {
        return [
            'title' => $this->translator->trans('media.mediatheque.trash.title', domain: 'media'),
            'text_folder' => $this->translator->trans('media.mediatheque.trash.text.folder', domain: 'media'),
            'text_media' => $this->translator->trans('media.mediatheque.trash.text.media', domain: 'media'),
            'text_info' => $this->translator->trans('media.mediatheque.trash.text.info', domain: 'media'),
            'loading' => $this->translator->trans('media.mediatheque.trash.loading', domain: 'media'),
            'btn_confirm' => $this->translator->trans('media.mediatheque.trash.btn.confirm', domain: 'media'),
            'btn_cancel' => $this->translator->trans('media.mediatheque.trash.btn.cancel', domain: 'media'),
            'success_trash' => $this->translator->trans('media.mediatheque.trash.success.trash', domain: 'media'),
            'success_no_trash' => $this->translator->trans('media.mediatheque.trash.success.no.trash', domain: 'media'),
            'breadcrumb_trash' => $this->translator->trans('media.mediatheque.trash.breadcrumb', domain: 'media'),
            'btn_close_trash' => $this->translator->trans('media.mediatheque.trash.btn.close', domain: 'media'),
            'link_revert' => $this->translator->trans('media.mediatheque.trash.link.revert', domain: 'media'),
            'link_delete' => $this->translator->trans('media.mediatheque.trash.link.delete', domain: 'media'),
            'no_media' => $this->translator->trans('media.mediatheque.trash.empty.media', domain: 'media'),
        ];
    }

    /**
     * Retourne les traductions pour l'action move
     * @return array
     */
    private function getMoveTranslate(): array
    {
        return [
            'title' => $this->translator->trans('media.mediatheque.move.title', domain: 'media'),
            'btn_close' => $this->translator->trans('media.mediatheque.move.btn.close', domain: 'media'),
            'btn_validate' => $this->translator->trans('media.mediatheque.move.btn.validate', domain: 'media'),
            'input_label' => $this->translator->trans('media.mediatheque.move.input.label', domain: 'media'),
            'input_info' => $this->translator->trans('media.mediatheque.move.input.info', domain: 'media'),
            'loading' => $this->translator->trans('media.mediatheque.move.loading', domain: 'media'),
            'success' => $this->translator->trans('media.mediatheque.move.success', domain: 'media'),
        ];
    }

    /**
     * Retourne les traductions liées au média
     * @return array
     */
    private function getMediaTranslate(): array
    {
        return [
            'link_info' => $this->translator->trans('media.mediatheque.media.link.info', domain: 'media'),
            'link_edit' => $this->translator->trans('media.mediatheque.media.link.edit', domain: 'media'),
            'link_edit_media' => $this->translator->trans('media.mediatheque.media.link.edit.media', domain: 'media'),
            'link_move' => $this->translator->trans('media.mediatheque.media.link.move', domain: 'media'),
            'link_remove' => $this->translator->trans('media.mediatheque.media.link.remove', domain: 'media'),
            'no_media' => $this->translator->trans('media.mediatheque.media.no.media', domain: 'media'),
            'table_name' => $this->translator->trans('media.mediatheque.media.table.name', domain: 'media'),
            'table_size' => $this->translator->trans('media.mediatheque.media.table.size', domain: 'media'),
            'table_action' => $this->translator->trans('media.mediatheque.media.table.action', domain: 'media'),
            'table_caption' => $this->translator->trans('media.mediatheque.media.table.caption', domain: 'media'),
            'table_date' => $this->translator->trans('media.mediatheque.media.table.date', domain: 'media'),
        ];
    }

    /**
     * Retourne les traductions pour les dossiers
     * @return array
     */
    private function getFolderTranslate(): array
    {
        return [
            'new' => $this->translator->trans('media.mediatheque.folder.new', domain: 'media'),
            'edit' => $this->translator->trans('media.mediatheque.folder.edit', domain: 'media'),
            'input_label' => $this->translator->trans('media.mediatheque.folder.input.label', domain: 'media'),
            'input_label_placeholder' => $this->translator->trans(
                'media.mediatheque.folder.input.label.placeholder',
                domain: 'media',
            ),
            'input_error' => $this->translator->trans('media.mediatheque.folder.input.error', domain: 'media'),
            'btn_submit_create' => $this->translator->trans(
                'media.mediatheque.folder.btn.submit.create',
                domain: 'media',
            ),
            'btn_submit_edit' => $this->translator->trans('media.mediatheque.folder.btn.submit.edit', domain: 'media'),
            'btn_cancel' => $this->translator->trans('media.mediatheque.folder.btn.cancel', domain: 'media'),
            'msg_wait_create' => $this->translator->trans('media.mediatheque.folder.msg.wait.create', domain: 'media'),
            'msg_wait_edit' => $this->translator->trans('media.mediatheque.folder.msg.wait.edit', domain: 'media'),
        ];
    }

    /**
     * Retourne les traductions pour les infos
     * @return array
     */
    private function getInfoTranslate(): array
    {
        return [
            'title' => $this->translator->trans('media.mediatheque.info.title', domain: 'media'),
            'btn_close' => $this->translator->trans('media.mediatheque.info.btn.close', domain: 'media'),
            'link_size' => $this->translator->trans('media.mediatheque.info.link.size', domain: 'media'),
            'link_download' => $this->translator->trans('media.mediatheque.info.link.download', domain: 'media'),
        ];
    }

    /**
     * Retourne les traductions pour l'upload
     * @return array
     */
    private function getUploadTranslate(): array
    {
        return [
            'title' => $this->translator->trans('media.mediatheque.upload.title', domain: 'media'),
            'help' => $this->translator->trans('media.mediatheque.upload.help', domain: 'media'),
            'form_title' => $this->translator->trans('media.mediatheque.upload.form.title', domain: 'media'),
            'input_upload' => $this->translator->trans('media.mediatheque.upload.input.upload', domain: 'media'),
            'btn_close' => $this->translator->trans('media.mediatheque.upload.btn.close', domain: 'media'),
            'input_title' => $this->translator->trans('media.mediatheque.upload.input.title', domain: 'media'),
            'input_title_help' => $this->translator->trans(
                'media.mediatheque.upload.input.title.help',
                domain: 'media',
            ),
            'input_description' => $this->translator->trans(
                'media.mediatheque.upload.input.description',
                domain: 'media',
            ),
            'input_description_help' => $this->translator->trans(
                'media.mediatheque.upload.input.description.help',
                domain: 'media',
            ),
            'btn_cancel' => $this->translator->trans('media.mediatheque.upload.btn.cancel', domain: 'media'),
            'btn_upload' => $this->translator->trans('media.mediatheque.upload.btn.upload', domain: 'media'),
            'error_title' => $this->translator->trans('media.mediatheque.upload.error.title', domain: 'media'),
            'error_size' => $this->translator->trans('media.mediatheque.upload.error.size', domain: 'media'),
            'error_ext' => $this->translator->trans('media.mediatheque.upload.error.ext', domain: 'media'),
            'no_preview' => $this->translator->trans('media.mediatheque.upload.no_preview', domain: 'media'),
            'preview' => $this->translator->trans('media.mediatheque.upload.preview', domain: 'media'),
            'preview_help' => $this->translator->trans('media.mediatheque.upload.preview.help', domain: 'media'),
            'loading_msg' => $this->translator->trans('media.mediatheque.upload.loading.msg', domain: 'media'),
            'loading_msg_success' => $this->translator->trans(
                'media.mediatheque.upload.loading.msg.success',
                domain: 'media',
            ),
        ];
    }

    /**
     * Retourne les traductions pour l'édition d'un média
     * @return array
     */
    private function getEditMediaTranslate(): array
    {
        return [
            'title' => $this->translator->trans('media.mediatheque.edit.media.title', domain: 'media'),
            'legend' => $this->translator->trans('media.mediatheque.edit.media.legend', domain: 'media'),
            'media_name' => $this->translator->trans('media.mediatheque.edit.media.name', domain: 'media'),
            'media_name_placeholder' => $this->translator->trans(
                'media.mediatheque.edit.media.name.placeholder',
                domain: 'media',
            ),
            'media_description' => $this->translator->trans(
                'media.mediatheque.edit.media.description',
                domain: 'media',
            ),
            'media_description_placeholder' => $this->translator->trans(
                'media.mediatheque.edit.media.description.placeholder',
                domain: 'media',
            ),
            'submit' => $this->translator->trans('media.mediatheque.edit.media.submit', domain: 'media'),
            'cancel' => $this->translator->trans('media.mediatheque.edit.media.cancel', domain: 'media'),
            'loading' => $this->translator->trans('media.mediatheque.edit.media.loading', domain: 'media'),
            'success' => $this->translator->trans('media.mediatheque.edit.media.success', domain: 'media'),
        ];
    }
}
