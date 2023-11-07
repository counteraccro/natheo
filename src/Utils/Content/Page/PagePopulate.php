<?php
/**
 * Permet de merger les données venant d'un tableau à un objet page
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Content\Page;

use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\Content\Tag\Tag;
use App\Service\Admin\Content\Page\PageService;

class PagePopulate
{
    /**
     * Clé pageTranslations
     * @var string
     */
    const KEY_PAGE_TRANSLATION = 'pageTranslations';

    const KEY_TAGS = 'tags';

    /**
     * @var Page
     */
    private Page $page;

    /**
     * @var array
     */
    private array $populate;

    /**
     * @var PageService
     */
    private PageService $pageService;

    /**
     * @param Page $page
     * @param array $populate
     * @param PageService $pageService
     */
    public function __construct(Page $page, array $populate, PageService $pageService)
    {
        $this->page = $page;
        $this->populate = $populate;
        $this->pageService = $pageService;
    }

    /**
     * Merge les données de populate dans l'objet $page
     * @return $this
     */
    public function populate(): static
    {
        $this->populatePage();
        $this->populatePageTranslation();
        $this->populateTags();
        return $this;
    }

    /**
     * Met à jour certaines données de l'objet Page
     * @return void
     */
    private function populatePage(): void
    {
        $this->page->setRender($this->populate['render']);
        $this->page->setStatus($this->populate['status']);
    }

    /**
     * Met à jour l'objet pageTranslation avec $populate si la clé
     * 'pageTranslations' est présente
     * @return void
     */
    private function populatePageTranslation(): void
    {
        if (isset($this->populate[self::KEY_PAGE_TRANSLATION])) {
            foreach ($this->page->getPageTranslations() as &$pageTranslation) {
                foreach ($this->populate[self::KEY_PAGE_TRANSLATION] as $dataTranslation) {
                    if ($pageTranslation->getLocale() === $dataTranslation['locale']) {
                        $pageTranslation = $this->mergeData($pageTranslation, $dataTranslation,
                            ['id', 'page', 'locale']);
                    }
                }
            }
        }
    }

    /**
     * Reset les tags présents dans $page et ajoute à la place les tags
     * associés à la page avec $populate si la clé 'tags' est présente
     * @return void
     */
    private function populateTags(): void
    {
        if (isset($this->populate[self::KEY_TAGS])) {
            $this->page->getTags()->clear();
            foreach ($this->populate[self::KEY_TAGS] as $dataTag) {
                $tag = $this->pageService->findOneById(Tag::class, $dataTag['id']);
                $this->page->addTag($tag);
            }
        }
    }


    /**
     * Retourne un objet Page
     * @return Page
     */
    public function getPage(): Page
    {
        return $this->page;
    }

    /**
     * Merge des données de $populate dans $object sans prendre en compte $exclude
     * @param mixed $object
     * @param array $populate
     * @param array $exclude
     * @return mixed
     */
    private function mergeData(mixed $object, array $populate, array $exclude = []): mixed
    {
        foreach ($populate as $key => $value) {
            if (in_array($key, $exclude)) {
                continue;
            }

            $func = 'set' . ucfirst($key);
            $object->$func($value);
        }
        return $object;
    }
}
