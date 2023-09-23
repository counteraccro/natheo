<?php
/**
 * Service pour la génération de l'éditeur Markdown du site
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Service\Admin;

class MarkdownEditorService extends AppAdminService
{
    /**
     *  Retourne les traductions de l'éditeur Markdown
     * @return array
     */
    public function getTranslate(): array
    {
        return [
            'btnBold' => $this->translator->trans('editor.button.bold', domain: 'editor_markdown'),
            'btnItalic' => $this->translator->trans('editor.button.italic', domain: 'editor_markdown'),
            'btnStrike' => $this->translator->trans('editor.button.strike', domain: 'editor_markdown'),
            'btnQuote' => $this->translator->trans('editor.button.quote', domain: 'editor_markdown'),
            'btnList' => $this->translator->trans('editor.button.list', domain: 'editor_markdown'),
            'btnListNumber' => $this->translator->trans('editor.button.list.number', domain: 'editor_markdown'),
            'btnTable' => $this->translator->trans('editor.button.table', domain: 'editor_markdown'),
            'btnLink' => $this->translator->trans('editor.button.link', domain: 'editor_markdown'),
            'btnImage' => $this->translator->trans('editor.button.image', domain: 'editor_markdown'),
            'btnCode' => $this->translator->trans('editor.button.code', domain: 'editor_markdown'),
            'btnSave' => $this->translator->trans('editor.button.save', domain: 'editor_markdown'),
            'btnKeyWord' => $this->translator->trans('editor.button.keyword', domain: 'editor_markdown'),
            'titreLabel'  => $this->translator->trans('editor.titre.label', domain: 'editor_markdown'),
            'titreH1'  => $this->translator->trans('editor.titre.h1', domain: 'editor_markdown'),
            'titreH2'  => $this->translator->trans('editor.titre.h2', domain: 'editor_markdown'),
            'titreH3'  => $this->translator->trans('editor.titre.h3', domain: 'editor_markdown'),
            'titreH4'  => $this->translator->trans('editor.titre.h4', domain: 'editor_markdown'),
            'titreH5'  => $this->translator->trans('editor.titre.h5', domain: 'editor_markdown'),
            'titreH6'  => $this->translator->trans('editor.titre.h6', domain: 'editor_markdown'),
            'help'  => $this->translator->trans('editor.help', domain: 'editor_markdown'),
            'render'  => $this->translator->trans('editor.render', domain: 'editor_markdown'),
            'modalTitreLink' => $this->translator->trans('editor.modal.titre.link', domain: 'editor_markdown'),
            'modalInputUrlLink' => $this->translator->trans('editor.modal.input.link', domain: 'editor_markdown'),
            'modalTitreImage' => $this->translator->trans('editor.modal.titre.image', domain: 'editor_markdown'),
            'modalInputUrlImage' => $this->translator->trans('editor.modal.input.image', domain: 'editor_markdown'),
            'modalBtnClose' => $this->translator->trans('editor.modal.button.close', domain: 'editor_markdown'),
            'modalBtnValide' => $this->translator->trans('editor.modal.button.valide', domain: 'editor_markdown'),
            'modalInputText' => $this->translator->trans('editor.modal.input.text', domain: 'editor_markdown'),
            'msgEmptyContent' => $this->translator->trans('editor.input.empty', domain: 'editor_markdown'),
            'btnMediatheque' => $this->translator->trans('editor.btn.mediatheque', domain: 'editor_markdown'),
            'mediathequeMarkdown' => [
                'title' => $this->translator->trans('editor.mediatheque.title', domain: 'editor_markdown'),
                'btn_close' => $this->translator->trans('editor.mediatheque.btn.close', domain: 'editor_markdown'),
                'loading' => $this->translator->trans('editor.mediatheque.loading', domain: 'editor_markdown'),
            ]
        ];
    }
}
