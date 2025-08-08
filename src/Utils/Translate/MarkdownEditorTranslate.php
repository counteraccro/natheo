<?php
/**
 * Class pour la génération des traductions pour les scripts vue pour MarkdownEditor
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Translate;

class MarkdownEditorTranslate extends AppTranslate
{
    /**
     * Génération du tableau de translate pour le script vue de markdownEditor
     * @return array
     */
    public function getTranslate(): array
    {
        return [
            'loading' => $this->translator->trans('editor.loading', domain: 'editor_markdown'),
            'btnBold' => $this->translator->trans('editor.button.bold', domain: 'editor_markdown'),
            'btnItalic' => $this->translator->trans('editor.button.italic', domain: 'editor_markdown'),
            'btnStrike' => $this->translator->trans('editor.button.strike', domain: 'editor_markdown'),
            'btnQuote' => $this->translator->trans('editor.button.quote', domain: 'editor_markdown'),
            'btnList' => $this->translator->trans('editor.button.list', domain: 'editor_markdown'),
            'btnListNumber' => $this->translator->trans('editor.button.list.number', domain: 'editor_markdown'),
            'btnTable' => $this->translator->trans('editor.button.table', domain: 'editor_markdown'),
            'btnLink' => $this->translator->trans('editor.button.link', domain: 'editor_markdown'),
            'btnLinkInterne' => $this->translator->trans('editor.button.link.interne', domain: 'editor_markdown'),
            'btnImage' => $this->translator->trans('editor.button.image', domain: 'editor_markdown'),
            'btnCode' => $this->translator->trans('editor.button.code', domain: 'editor_markdown'),
            'btnSave' => $this->translator->trans('editor.button.save', domain: 'editor_markdown'),
            'btnKeyWord' => $this->translator->trans('editor.button.keyword', domain: 'editor_markdown'),
            'titreLabel' => $this->translator->trans('editor.titre.label', domain: 'editor_markdown'),
            'titreH1' => $this->translator->trans('editor.titre.h1', domain: 'editor_markdown'),
            'titreH2' => $this->translator->trans('editor.titre.h2', domain: 'editor_markdown'),
            'titreH3' => $this->translator->trans('editor.titre.h3', domain: 'editor_markdown'),
            'titreH4' => $this->translator->trans('editor.titre.h4', domain: 'editor_markdown'),
            'titreH5' => $this->translator->trans('editor.titre.h5', domain: 'editor_markdown'),
            'titreH6' => $this->translator->trans('editor.titre.h6', domain: 'editor_markdown'),
            'preview' => $this->translator->trans('editor.button.preview', domain: 'editor_markdown'),
            'help' => $this->translator->trans('editor.help', domain: 'editor_markdown'),
            'render' => $this->translator->trans('editor.render', domain: 'editor_markdown'),
            'modalTitreLink' => $this->translator->trans('editor.modal.titre.link', domain: 'editor_markdown'),
            'modalInputUrlLink' => $this->translator->trans('editor.modal.input.link', domain: 'editor_markdown'),
            'modalTitreImage' => $this->translator->trans('editor.modal.titre.image', domain: 'editor_markdown'),
            'modalInputUrlImage' => $this->translator->trans('editor.modal.input.image', domain: 'editor_markdown'),
            'modalBtnClose' => $this->translator->trans('editor.modal.button.close', domain: 'editor_markdown'),
            'modalBtnValide' => $this->translator->trans('editor.modal.button.valide', domain: 'editor_markdown'),
            'modalInputText' => $this->translator->trans('editor.modal.input.text', domain: 'editor_markdown'),
            'msgEmptyContent' => $this->translator->trans('editor.input.empty', domain: 'editor_markdown'),
            'btnMediatheque' => $this->translator->trans('editor.btn.mediatheque', domain: 'editor_markdown'),
            'warning_edit' => $this->translator->trans('editor.warning.edit', domain: 'editor_markdown'),
            'mediathequeMarkdown' => $this->getTranslateMediateque(),
            'modaleInternalLink' => $this->getTranslateModaleInternalLink()
        ];
    }

    /**
     * Traduction de la modale internal link
     * @return array
     */
    private function getTranslateModaleInternalLink(): array
    {
        return [
            'title' => $this->translator->trans('editor.modale.internal.link.title', domain: 'editor_markdown'),
            'labelSearch' => $this->translator->trans('editor.modale.internal.link.label.search', domain: 'editor_markdown'),
            'labelText' => $this->translator->trans('editor.modale.internal.link.label.text', domain: 'editor_markdown'),
            'placeHolderSearch' => $this->translator->trans('editor.modale.internal.link.place.holder.search', domain: 'editor_markdown'),
            'placeHolderTxt' => $this->translator->trans('editor.modale.internal.link.place.holder.text', domain: 'editor_markdown'),
        ];
    }

    /**
     * Génère le boc médiatheque
     * @return array
     */
    public function getTranslateMediateque(): array
    {
        return [
            'title' => $this->translator->trans('editor.mediatheque.title', domain: 'editor_markdown'),
            'btn_close' => $this->translator->trans('editor.mediatheque.btn.close', domain: 'editor_markdown'),
            'loading' => $this->translator->trans('editor.mediatheque.loading', domain: 'editor_markdown'),
            'no_media' => $this->translator->trans('editor.mediatheque.no_media', domain: 'editor_markdown'),
            'btn_size' => $this->translator->trans('editor.mediatheque.btn.size', domain: 'editor_markdown'),
            'size_fluide' => $this->translator->trans('editor.mediatheque.size.fluide', domain: 'editor_markdown'),
            'size_max' => $this->translator->trans('editor.mediatheque.size_max', domain: 'editor_markdown'),
            'size_100' => $this->translator->trans('editor.mediatheque.size_100', domain: 'editor_markdown'),
            'size_200' => $this->translator->trans('editor.mediatheque.size_200', domain: 'editor_markdown'),
            'size_300' => $this->translator->trans('editor.mediatheque.size_300', domain: 'editor_markdown'),
            'size_400' => $this->translator->trans('editor.mediatheque.size_400', domain: 'editor_markdown'),
            'size_500' => $this->translator->trans('editor.mediatheque.size_500', domain: 'editor_markdown'),
        ];
    }
}
