<?php

declare(strict_types=1);
/**
 * Service pour la génération de l'éditeur Markdown du site
 * @author Gourdon Aymeric
 * @version 1.3
 */

namespace App\Service\Admin;

use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\Content\Page\PageTranslation;
use App\Enum\Admin\Content\Page\PageStatus;
use App\Enum\Admin\System\Options\OptionSystem;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class MarkdownEditorService extends AppAdminService
{
    /**
     * Transforme certaines balises markdown custom en balise markdown officielle
     * @param string $markdown
     * @param string|null $locale langue des liens internes, langue courante si null
     * @param bool $absoluteUrls true pour préfixer les urls relatives par l'adresse du site (ex. emails)
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function parseMarkdown(string $markdown, ?string $locale = null, bool $absoluteUrls = false): string
    {
        $markdown = $this->parseInternalLink($markdown, $locale ?? $this->getLocales()['current']);
        if ($absoluteUrls) {
            $markdown = $this->makeUrlsAbsolute($markdown);
        }
        return $markdown;
    }

    /**
     * Préfixe par l'adresse du site les cibles relatives ("/...") des liens et images markdown
     * @param string $text
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function makeUrlsAbsolute(string $text): string
    {
        $siteUrl = $this->getSiteUrl();
        // "](/x" et "](</x" ; "//" (url sans protocole) est laissé tel quel
        return preg_replace_callback('/(?<=\]\()(<?)\/(?!\/)/', fn(array $match) => $match[1] . $siteUrl . '/', $text);
    }

    /**
     * Adresse du site sans "/" final
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getSiteUrl(): string
    {
        $url = (string) $this->getOptionSystemService()->getValueByKey(OptionSystem::OS_ADRESSE_SITE->value);
        return rtrim($url, '/');
    }

    /**
     * Génère les liens internes du CMS : remplace la cible "P#id" d'un lien markdown par l'url de la page
     * Une page inexistante, non publiée, désactivée ou non traduite produit un lien "#"
     * @param string $text
     * @param string $locale
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function parseInternalLink(string $text, string $locale): string
    {
        if (!str_contains($text, 'P#')) {
            return $text;
        }

        $url = $this->getSiteUrl();
        $tabCategories = $this->getPageService()->getAllCategories();

        $cache = [];
        return preg_replace_callback(
            '/(?<=\]\()P#(\d+)(?=[\s)])/',
            function (array $match) use (&$cache, $locale, $url, $tabCategories): string {
                $id = (int) $match[1];
                if (!isset($cache[$id])) {
                    $cache[$id] = $this->generateInternalUrl($id, $locale, $url, $tabCategories);
                }
                return $cache[$id];
            },
            $text,
        );
    }

    /**
     * Construit l'url publique d'une page
     * @param int $id
     * @param string $locale
     * @param string $url
     * @param array $tabCategories
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function generateInternalUrl(int $id, string $locale, string $url, array $tabCategories): string
    {
        /** @var Page|null $page */
        $page = $this->findOneById(Page::class, $id);
        if ($page === null || $page->isDisabled() || $page->getStatus() !== PageStatus::PUBLISH->value) {
            return '#';
        }

        $pageTrans = $page
            ->getPageTranslations()
            ->filter(fn(PageTranslation $translation) => $translation->getLocale() === $locale)
            ->first();
        if (!($pageTrans instanceof PageTranslation)) {
            return '#';
        }

        $category = strtolower($tabCategories[$page->getCategory()] ?? '');
        return $url . '/' . $locale . '/' . $category . '/' . $pageTrans->getUrl();
    }
}
