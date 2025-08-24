<?php
/**
 * Service pour la génération de l'éditeur Markdown du site
 * @author Gourdon Aymeric
 * @version 1.2
 */

namespace App\Service\Admin;

use App\Entity\Admin\Content\Page\Page;
use App\Utils\Content\Page\PageConst;
use App\Utils\System\Options\OptionSystemKey;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class MarkdownEditorService extends AppAdminService
{

    /**
     * Transforme certaines balises markdown custom en balise markdown officielle
     * @param string $markdown
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function parseMarkdown(string $markdown): string
    {
        $markdown = $this->parseInternalLink($markdown);
        return $markdown;
    }

    /**
     * Génère les liens internes du CMS
     * @param string $text
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function parseInternalLink(string $text): string
    {
        $locale = $this->getLocales()['current'];
        $url = $this->getOptionSystemService()->getByKey(OptionSystemKey::OS_ADRESSE_SITE)->getValue();
        $tabCategories = $this->getPageService()->getAllCategories();

        $re = '/(P#(\d))/m';
        preg_match_all($re, $text, $matches, PREG_SET_ORDER, 0);

        foreach($matches as $match) {
            /** @var Page $page */
            $page = $this->findOneById(Page::class, $match[2]);
            $pageTrans = $page->getPageTranslationByLocale($locale);
            $urlGenerate = $url . '/' . $locale . '/' . strtolower($tabCategories[$page->getCategory()]) . '/' . $pageTrans->getUrl();
            $text = str_replace($match[0], $urlGenerate, $text);
        }
        return $text;
    }

    /**
     *  Retourne les traductions de l'éditeur Markdown
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @deprecated
     */
    public function getTranslate(): array
    {
        $translator = $this->getTranslator();

        trigger_deprecation('Service/Admin/MarkdownEditorService', '1.0',
            'Méthode "%s()" dépréciée, passer maintenant par MarkdownEditorTranslate pour avoir la traduction.',
            __METHOD__ );
        return [
            'btnBold' => $translator->trans('editor.button.bold', domain: 'editor_markdown'),
            'btnItalic' => $translator->trans('editor.button.italic', domain: 'editor_markdown'),
            'btnStrike' => $translator->trans('editor.button.strike', domain: 'editor_markdown'),
            'btnQuote' => $translator->trans('editor.button.quote', domain: 'editor_markdown'),
            'btnList' => $translator->trans('editor.button.list', domain: 'editor_markdown'),
            'btnListNumber' => $translator->trans('editor.button.list.number', domain: 'editor_markdown'),
            'btnTable' => $translator->trans('editor.button.table', domain: 'editor_markdown'),
            'btnLink' => $translator->trans('editor.button.link', domain: 'editor_markdown'),
            'btnImage' => $translator->trans('editor.button.image', domain: 'editor_markdown'),

            'btnCode' => $translator->trans('editor.button.code', domain: 'editor_markdown'),
            'btnSave' => $translator->trans('editor.button.save', domain: 'editor_markdown'),
            'btnKeyWord' => $translator->trans('editor.button.keyword', domain: 'editor_markdown'),
            'titreLabel'  => $translator->trans('editor.titre.label', domain: 'editor_markdown'),
            'titreH1'  => $translator->trans('editor.titre.h1', domain: 'editor_markdown'),
            'titreH2'  => $translator->trans('editor.titre.h2', domain: 'editor_markdown'),
            'titreH3'  => $translator->trans('editor.titre.h3', domain: 'editor_markdown'),
            'titreH4'  => $translator->trans('editor.titre.h4', domain: 'editor_markdown'),
            'titreH5'  => $translator->trans('editor.titre.h5', domain: 'editor_markdown'),
            'titreH6'  => $translator->trans('editor.titre.h6', domain: 'editor_markdown'),
            'help'  => $translator->trans('editor.help', domain: 'editor_markdown'),
            'render'  => $translator->trans('editor.render', domain: 'editor_markdown'),
            'modalTitreLink' => $translator->trans('editor.modal.titre.link', domain: 'editor_markdown'),
            'modalInputUrlLink' => $translator->trans('editor.modal.input.link', domain: 'editor_markdown'),
            'modalTitreImage' => $translator->trans('editor.modal.titre.image', domain: 'editor_markdown'),
            'modalInputUrlImage' => $translator->trans('editor.modal.input.image', domain: 'editor_markdown'),
            'modalBtnClose' => $translator->trans('editor.modal.button.close', domain: 'editor_markdown'),
            'modalBtnValide' => $translator->trans('editor.modal.button.valide', domain: 'editor_markdown'),
            'modalInputText' => $translator->trans('editor.modal.input.text', domain: 'editor_markdown'),
            'msgEmptyContent' => $translator->trans('editor.input.empty', domain: 'editor_markdown'),
            'btnMediatheque' => $translator->trans('editor.btn.mediatheque', domain: 'editor_markdown'),
            'warning_edit'  => $translator->trans('editor.warning.edit', domain: 'editor_markdown'),
            'mediathequeMarkdown' => [
                'title' => $translator->trans('editor.mediatheque.title', domain: 'editor_markdown'),
                'btn_close' => $translator->trans('editor.mediatheque.btn.close', domain: 'editor_markdown'),
                'loading' => $translator->trans('editor.mediatheque.loading', domain: 'editor_markdown'),
                'no_media' => $translator->trans('editor.mediatheque.no_media', domain: 'editor_markdown'),
                'btn_size' => $translator->trans('editor.mediatheque.btn.size', domain: 'editor_markdown'),
                'size_fluide' => $translator->trans('editor.mediatheque.size.fluide', domain: 'editor_markdown'),
                'size_max' => $translator->trans('editor.mediatheque.size_max', domain: 'editor_markdown'),
                'size_100' => $translator->trans('editor.mediatheque.size_100', domain: 'editor_markdown'),
                'size_200' => $translator->trans('editor.mediatheque.size_200', domain: 'editor_markdown'),
                'size_300' => $translator->trans('editor.mediatheque.size_300', domain: 'editor_markdown'),
                'size_400' => $translator->trans('editor.mediatheque.size_400', domain: 'editor_markdown'),
                'size_500' => $translator->trans('editor.mediatheque.size_500', domain: 'editor_markdown'),

            ]
        ];
    }
}
