<?php

declare(strict_types=1);
/**
 * Class pour la génération des traductions pour les scripts vue pour Mediatheque
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Translate\Admin\Content;

use App\Translate\AppTranslate;

class MediaTranslate extends AppTranslate
{
    /**
     * Retourne la traduction de la médiathèque
     * @return array
     */
    public function getTranslate(): array
    {
        return [
            'loading' => $this->translator->trans('media.library.loading', domain: 'media'),
            'generic_error' => $this->translator->trans('media.library.generic.error', domain: 'media'),
            'btn_new_folder' => $this->translator->trans('media.library.button.new.folder', domain: 'media'),
            'btn_new_media' => $this->translator->trans('media.library.button.new.media', domain: 'media'),
            'header_trash' => $this->translator->trans('media.library.header.trash', domain: 'media'),
            'nb_file_trash' => $this->translator->trans('media.library.nb.files.trash', domain: 'media'),
            'btn_filtre' => $this->translator->trans('media.library.button.filter', domain: 'media'),
            'filtre_date' => $this->translator->trans('media.library.filter.date', domain: 'media'),
            'filtre_nom' => $this->translator->trans('media.library.filter.name', domain: 'media'),
            'filtre_type' => $this->translator->trans('media.library.filter.type', domain: 'media'),
            'search_placeholder' => $this->translator->trans('media.library.search.placeholder', domain: 'media'),
            'disque_size' => $this->translator->trans('media.library.disk_size', domain: 'media'),
            'success_add_trash' => $this->translator->trans('media.library.success.add.trash', domain: 'media'),
            'success_remove_trash' => $this->translator->trans(
                'media.library.success.remove.trash',
                domain: 'media',
            ),
            'media' => $this->getMediaTranslate(),
            'folder' => $this->getFolderTranslate(),
            'info' => $this->getInfoTranslate(),
            'upload' => $this->getUploadTranslate(),
            'edit' => $this->getEditTranslate(),
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
            'title' => $this->translator->trans('media.library.remove.title', domain: 'media'),
            'loading' => $this->translator->trans('media.library.remove.loading', domain: 'media'),
            'btn_cancel' => $this->translator->trans('media.library.remove.button.cancel', domain: 'media'),
            'success' => $this->translator->trans('media.library.remove.success', domain: 'media'),
        ];
    }

    /**
     * Retourne les traductions pour l'action trash
     * @return array
     */
    private function getTrashTranslate(): array
    {
        return [
            'title' => $this->translator->trans('media.library.trash.title', domain: 'media'),
            'loading' => $this->translator->trans('media.library.trash.loading', domain: 'media'),
            'confirm_trash_title' => $this->translator->trans(
                'media.library.delete.confirm.title',
                domain: 'media',
            ),
            'confirm_trash_yes' => $this->translator->trans('media.library.delete.button.confirm', domain: 'media'),
            'confirm_trash_no' => $this->translator->trans('media.library.delete.button.cancel', domain: 'media'),
            'link_revert' => $this->translator->trans('media.library.trash.link.revert', domain: 'media'),
            'link_delete' => $this->translator->trans('media.library.trash.link.delete', domain: 'media'),
            'no_media' => $this->translator->trans('media.library.trash.empty.media', domain: 'media'),
            'folder_tag' => $this->translator->trans('media.library.media.folder.tag', domain: 'media'),
            'folder_empty' => $this->translator->trans('media.library.media.folder.empty', domain: 'media'),
            'empty_trash_title' => $this->translator->trans('media.library.trash.empty.title', domain: 'media'),
            'empty_trash_sub' => $this->translator->trans('media.library.trash.empty.title_sub', domain: 'media'),
        ];
    }

    /**
     * Retourne les traductions pour l'action move
     * @return array
     */
    private function getMoveTranslate(): array
    {
        return [
            'title' => $this->translator->trans('media.library.move.title', domain: 'media'),
            'btn_move' => $this->translator->trans('media.library.move.button', domain: 'media'),
            'move_ok' => $this->translator->trans('media.library.move.success', domain: 'media'),
            'btn_error' => $this->translator->trans('media.library.move.button_error', domain: 'media'),
            'search_placeholder' => $this->translator->trans(
                'media.library.move.search.placeholder',
                domain: 'media',
            ),
            'msg_no_search' => $this->translator->trans('media.library.move.search.empty.message', domain: 'media'),
            'msg_no_search_sub' => $this->translator->trans(
                'media.library.move.search.empty.message_sub',
                domain: 'media',
            ),
            'move_success' => $this->translator->trans('media.library.move.toast.success', domain: 'media'),
            'generic_error' => $this->translator->trans('media.library.generic.error', domain: 'media'),
            'sub_title_media' => $this->translator->trans('media.library.move.sub.title.media', domain: 'media'),
            'sub_title_folder' => $this->translator->trans('media.library.move.sub.title.folder', domain: 'media'),
            'sub_title_end' => $this->translator->trans('media.library.move.sub.title.end', domain: 'media'),
        ];
    }

    /**
     * Retourne les traductions liées au média
     * @return array
     */
    private function getMediaTranslate(): array
    {
        return [
            'link_info' => $this->translator->trans('media.library.media.link.info', domain: 'media'),
            'link_edit' => $this->translator->trans('media.library.media.link.edit', domain: 'media'),
            'link_move' => $this->translator->trans('media.library.media.link.move', domain: 'media'),
            'link_remove' => $this->translator->trans('media.library.media.link.remove', domain: 'media'),
            'no_media' => $this->translator->trans('media.library.media.no.media', domain: 'media'),
            'table_name' => $this->translator->trans('media.library.media.table.name', domain: 'media'),
            'table_type' => $this->translator->trans('media.library.media.table.type', domain: 'media'),
            'table_size' => $this->translator->trans('media.library.media.table.size', domain: 'media'),
            'table_action' => $this->translator->trans('media.library.media.table.action', domain: 'media'),
            'table_date' => $this->translator->trans('media.library.media.table.date', domain: 'media'),
            'folder_nb_element' => $this->translator->trans('media.library.media.folder_element', domain: 'media'),
            'folder_nb_elements' => $this->translator->trans(
                'media.library.media.folder_elements',
                domain: 'media',
            ),
            'media_other_img' => $this->translator->trans('media.library.media.other_img', domain: 'media'),
            'folder_tag' => $this->translator->trans('media.library.media.folder.tag', domain: 'media'),
            'folder_empty' => $this->translator->trans('media.library.media.folder.empty', domain: 'media'),
            'confirm_trash_title' => $this->translator->trans('media.library.trash.confirm.title', domain: 'media'),
            'confirm_trash_yes' => $this->translator->trans('media.library.trash.button.confirm', domain: 'media'),
            'confirm_trash_no' => $this->translator->trans('media.library.trash.button.cancel', domain: 'media'),
            'empty_folder_title' => $this->translator->trans('media.library.folder.empty.title', domain: 'media'),
            'empty_folder_sub' => $this->translator->trans('media.library.folder.empty.sub', domain: 'media'),
        ];
    }

    /**
     * Retourne les traductions pour les dossiers
     * @return array
     */
    private function getFolderTranslate(): array
    {
        return [
            'new' => $this->translator->trans('media.library.folder.new', domain: 'media'),
            'edit' => $this->translator->trans('media.library.folder.edit', domain: 'media'),
            'btn_cancel' => $this->translator->trans('media.library.folder.button.cancel', domain: 'media'),
        ];
    }

    /**
     * Retourne les traductions pour les infos
     * @return array
     */
    private function getInfoTranslate(): array
    {
        return [
            'title' => $this->translator->trans('media.library.info.title', domain: 'media'),
            'taille' => $this->translator->trans('media.library.info.size', domain: 'media'),
            'dimension' => $this->translator->trans('media.library.info.dimension', domain: 'media'),
            'created_at' => $this->translator->trans('media.library.info.created_at', domain: 'media'),
            'folder' => $this->translator->trans('media.library.info.folder', domain: 'media'),
            'url' => $this->translator->trans('media.library.info.url', domain: 'media'),
            'btn_copy' => $this->translator->trans('media.library.info.button.copy', domain: 'media'),
            'copy_ok' => $this->translator->trans('media.library.info.copy_ok', domain: 'media'),
            'content' => $this->translator->trans('media.library.info.content', domain: 'media'),
            'content_file' => $this->translator->trans('media.library.info.content_file', domain: 'media'),
            'content_folder' => $this->translator->trans('media.library.info.content_folder', domain: 'media'),
            'folder_tag' => $this->translator->trans('media.library.media.folder.tag', domain: 'media'),
        ];
    }

    /**
     * Retourne les traductions pour l'upload
     * @return array
     */
    private function getUploadTranslate(): array
    {
        return [
            'title' => $this->translator->trans('media.library.upload.title', domain: 'media'),
            'help' => $this->translator->trans('media.library.upload.help', domain: 'media'),
            'or' => $this->translator->trans('media.library.upload.or', domain: 'media'),
            'browse' => $this->translator->trans('media.library.upload.browse', domain: 'media'),
            'input_title' => $this->translator->trans('media.library.upload.input.title', domain: 'media'),
            'input_title_help' => $this->translator->trans(
                'media.library.upload.input.title_help',
                domain: 'media',
            ),
            'input_description' => $this->translator->trans(
                'media.library.upload.input.description',
                domain: 'media',
            ),
            'input_description_help' => $this->translator->trans(
                'media.library.upload.input.description_help',
                domain: 'media',
            ),
            'btn_cancel' => $this->translator->trans('media.library.upload.button.cancel', domain: 'media'),
            'btn_upload' => $this->translator->trans('media.library.upload.button.upload', domain: 'media'),
            'error_title' => $this->translator->trans('media.library.upload.error.title', domain: 'media'),
            'error_size' => $this->translator->trans('media.library.upload.error.size', domain: 'media'),
            'error_ext' => $this->translator->trans('media.library.upload.error.ext', domain: 'media'),
            'preview' => $this->translator->trans('media.library.upload.preview', domain: 'media'),
            'generic_error' => $this->translator->trans('media.library.generic.error', domain: 'media'),
            'loading_msg_success' => $this->translator->trans(
                'media.library.upload.loading.message.success',
                domain: 'media',
            ),
        ];
    }

    /**
     * Retourne les traductions pour l'édition d'un média
     * @return array
     */
    private function getEditTranslate(): array
    {
        return [
            'title' => $this->translator->trans('media.library.edit.title', domain: 'media'),
            'title_new_folder' => $this->translator->trans('media.library.edit.title_new_folder', domain: 'media'),
            'input_label_file' => $this->translator->trans('media.library.edit.input.label.file', domain: 'media'),
            'input_label_folder' => $this->translator->trans(
                'media.library.edit.input.label.folder',
                domain: 'media',
            ),
            'input_description' => $this->translator->trans(
                'media.library.edit.input.description',
                domain: 'media',
            ),
            'input_placeholder' => $this->translator->trans(
                'media.library.edit.input.placeholder',
                domain: 'media',
            ),
            'input_placeholder_folder' => $this->translator->trans(
                'media.library.edit.input.placeholder_folder',
                domain: 'media',
            ),
            'btn_save' => $this->translator->trans('media.library.edit.button.save', domain: 'media'),
            'btn_save_new_folder' => $this->translator->trans(
                'media.library.edit.button.save_new.folder',
                domain: 'media',
            ),
            'btn_error' => $this->translator->trans('media.library.edit.button.error', domain: 'media'),
            'save_ok' => $this->translator->trans('media.library.edit.button.save_ok', domain: 'media'),
            'save_media_msg_ok' => $this->translator->trans(
                'media.library.edit.save.media.message.ok',
                domain: 'media',
            ),
            'folder_tag' => $this->translator->trans('media.library.media.folder.tag', domain: 'media'),
        ];
    }

    /**
     * Message d'erreur : un dossier de ce nom existe déjà (au sein du même parent)
     * @return string
     */
    public function getFolderExistNameError(): string
    {
        return $this->translator->trans('media.library.folder.error.exist_name', domain: 'media');
    }

    /**
     * Message de succès : création d'un dossier
     * @param string $name
     * @return string
     */
    public function getFolderCreateSuccessMsg(string $name): string
    {
        return $this->translator->trans('media.library.folder.success', ['name' => $name], domain: 'media');
    }

    /**
     * Message de succès : renommage d'un dossier
     * @param string $newName
     * @param string $oldName
     * @return string
     */
    public function getFolderEditSuccessMsg(string $newName, string $oldName): string
    {
        return $this->translator->trans(
            'media.library.folder.edit_success',
            ['new_name' => $newName, 'name' => $oldName],
            domain: 'media',
        );
    }

    /**
     * Libellé de la modale de déplacement pour un média
     * @param string $name
     * @return string
     */
    public function getMoveLabelMedia(string $name): string
    {
        return $this->translator->trans('media.library.move.label.media', ['name' => $name], domain: 'media');
    }

    /**
     * Libellé de la modale de déplacement pour un dossier
     * @param string $name
     * @return string
     */
    public function getMoveLabelFolder(string $name): string
    {
        return $this->translator->trans('media.library.move.label.folder', ['name' => $name], domain: 'media');
    }
}
