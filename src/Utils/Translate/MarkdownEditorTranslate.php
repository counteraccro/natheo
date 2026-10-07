<?php

declare(strict_types=1);
/**
 * Class pour la génération des traductions pour les scripts vue pour MarkdownEditor
 * @author Gourdon Aymeric
 * @version 1.1
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
            'btnHeading' => $this->translator->trans('editor.button.heading', domain: 'editor_markdown'),
            'btnMediatheque' => $this->translator->trans('editor.btn.mediatheque', domain: 'editor_markdown'),
            'titreH1' => $this->translator->trans('editor.titre.h1', domain: 'editor_markdown'),
            'titreH2' => $this->translator->trans('editor.titre.h2', domain: 'editor_markdown'),
            'titreH3' => $this->translator->trans('editor.titre.h3', domain: 'editor_markdown'),
            'titreH4' => $this->translator->trans('editor.titre.h4', domain: 'editor_markdown'),
            'titreH5' => $this->translator->trans('editor.titre.h5', domain: 'editor_markdown'),
            'titreH6' => $this->translator->trans('editor.titre.h6', domain: 'editor_markdown'),
            'preview' => $this->translator->trans('editor.button.preview', domain: 'editor_markdown'),
            'emptyPreview' => $this->translator->trans('editor.emptyPreview', domain: 'editor_markdown'),
            'help' => $this->translator->trans('editor.help', domain: 'editor_markdown'),
            'msgEmptyContent' => $this->translator->trans('editor.input.empty', domain: 'editor_markdown'),
            'textareaPlaceholder' => $this->translator->trans('editor.textarea.placeholder', domain: 'editor_markdown'),
            'words' => $this->translator->trans('editor.words', domain: 'editor_markdown'),
            'caracteres' => $this->translator->trans('editor.caracteres', domain: 'editor_markdown'),
            'placeholders' => $this->getTranslatePlaceholders(),
            'modaleMediatheque' => $this->getTranslateMediateque(),
            'modaleInternalLink' => $this->getTranslateModaleInternalLink(),
        ];
    }

    /**
     * Textes insérés par défaut dans l'éditeur lorsqu'aucun texte n'est sélectionné
     * @return array
     */
    private function getTranslatePlaceholders(): array
    {
        return [
            'bold' => $this->translator->trans('editor.placeholder.bold', domain: 'editor_markdown'),
            'italic' => $this->translator->trans('editor.placeholder.italic', domain: 'editor_markdown'),
            'strike' => $this->translator->trans('editor.placeholder.strike', domain: 'editor_markdown'),
            'code' => $this->translator->trans('editor.placeholder.code', domain: 'editor_markdown'),
            'link' => $this->translator->trans('editor.placeholder.link', domain: 'editor_markdown'),
            'image' => $this->translator->trans('editor.placeholder.image', domain: 'editor_markdown'),
            'tableColumn' => $this->translator->trans('editor.placeholder.table.column', domain: 'editor_markdown'),
            'tableCell' => $this->translator->trans('editor.placeholder.table.cell', domain: 'editor_markdown'),
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
            'labelSearch' => $this->translator->trans(
                'editor.modale.internal.link.label.search',
                domain: 'editor_markdown',
            ),
            'noResult' => $this->translator->trans('editor.modale.internal.link.noResult', domain: 'editor_markdown'),
            'statistique' => $this->translator->trans('editor.modale.internal.link.stats', domain: 'editor_markdown'),
            'loading' => $this->translator->trans('editor.loading', domain: 'editor_markdown'),
            'error' => $this->translator->trans('editor.modale.internal.link.error', domain: 'editor_markdown'),
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
            'no_media' => $this->translator->trans('editor.mediatheque.no_media', domain: 'editor_markdown'),
            'no_search' => $this->translator->trans('editor.mediatheque.no_search', domain: 'editor_markdown'),
            'search_placeholder' => $this->translator->trans(
                'editor.mediatheque.search.placeholder',
                domain: 'editor_markdown',
            ),
            'folder' => $this->translator->trans('editor.mediatheque.folder', domain: 'editor_markdown'),
            'img' => $this->translator->trans('editor.mediatheque.img', domain: 'editor_markdown'),
            'file' => $this->translator->trans('editor.mediatheque.file', domain: 'editor_markdown'),
            'files' => $this->translator->trans('editor.mediatheque.files', domain: 'editor_markdown'),
            'root' => $this->translator->trans('editor.mediatheque.root', domain: 'editor_markdown'),
            'error' => $this->translator->trans('editor.mediatheque.error', domain: 'editor_markdown'),
        ];
    }
}
