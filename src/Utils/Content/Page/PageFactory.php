<?php
/**
 * Permet de créer une page ainsi que l'ensemble de ses données associées
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Content\Page;

use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\Content\Page\PageContent;
use App\Entity\Admin\Content\Page\PageContentTranslation;
use App\Entity\Admin\Content\Page\PageStatistique;
use App\Entity\Admin\Content\Page\PageTranslation;

class PageFactory
{

    /**
     * @var Page
     */
    private Page $page;

    /**
     * @var array
     */
    private array $locales;

    public function __construct(array $locales)
    {
        $this->locales = $locales;
    }

    /**
     * Créer un nouvel objet Page
     * @return Page
     */
    public function create(): Page
    {
        $this->page = new Page();
        $this->createPageTranslation();
        $this->createPageStatistique();
        $this->createPageContent();

        return $this->getPage();
    }

    /**
     * Créer les pageTranslation en fonctions des langues
     * @return void
     */
    public function createPageTranslation(): void
    {
        foreach ($this->locales as $locale) {
            $pageTranslation = new PageTranslation();
            $pageTranslation->setLocale($locale);
            $pageTranslation->setPage($this->page);
            $pageTranslation->setTitre('');
            $pageTranslation->setUrl('');
            $this->page->addPageTranslation($pageTranslation);
        }
    }

    /**
     * Création des pageContent
     * @return void
     */
    private function createPageContent(): void
    {
        $pageContent = new PageContent();
        $pageContent->setType(PageConst::CONTENT_TYPE_TEXT);
        $pageContent->setRenderOrder(1);

        foreach ($this->locales as $locale) {
            $pageContentTranslation = new PageContentTranslation();
            $pageContentTranslation->setLocale($locale);
            $pageContentTranslation->setText('[' . $locale . '] Contenu de votre page');
            $pageContentTranslation->setPageContent($pageContent);
            $pageContent->addPageContentTranslation($pageContentTranslation);
        }
        $pageContent->setPage($this->page);
        $this->page->addPageContent($pageContent);
    }

    /**
     * Créer les pagesStatistiques
     * @return void
     */
    private function createPageStatistique(): void
    {

        foreach (PageStatistiqueKey::getConstants() as $constant) {
            $pageStatistique = new PageStatistique();
            $pageStatistique->setKey($constant);
            $pageStatistique->setValue(0);
            $pageStatistique->setPage($this->page);
            $this->page->addPageStatistique($pageStatistique);
        }
    }

    /**
     * Retourne la page courante
     * @return Page
     */
    private function getPage(): Page
    {
        return $this->page;
    }
}
