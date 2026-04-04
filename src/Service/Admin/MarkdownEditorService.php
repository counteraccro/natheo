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

        $re = '/(P#(\d+))/m';
        preg_match_all($re, $text, $matches, PREG_SET_ORDER, 0);

        foreach ($matches as $match) {
            /** @var Page $page */
            $page = $this->findOneById(Page::class, $match[2]);

            $pageTrans = $page->getPageTranslationByLocale($locale);
            $urlGenerate =
                $url .
                '/' .
                $locale .
                '/' .
                strtolower($tabCategories[$page->getCategory()]) .
                '/' .
                $pageTrans->getUrl();
            $pattern = '/' . preg_quote($match[0], '/') . '(?!\d)/';
            $text = preg_replace($pattern, $urlGenerate, $text);
        }
        return $text;
    }
}
