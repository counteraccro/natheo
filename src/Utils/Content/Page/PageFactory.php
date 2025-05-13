<?php
/**
 * Permet de créer une page ainsi que l'ensemble de ses données associées
 * @author Gourdon Aymeric
 * @version 1.2
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
     * @var ?Page
     */
    private ?Page $page = null;

    /**
     * @var array
     */
    private array $locales;

    /**
     * @param array $locales
     */
    public function __construct(array $locales)
    {
        $this->locales = $locales;
    }

    /**
     * Créer un nouvel objet Page
     * @return PageFactory
     */
    public function create(): PageFactory
    {
        $this->page = new Page();
        $this->createPageTranslation();
        $this->createPageStatistique();
        $this->createPageContent();

        return $this;
    }

    /**
     * Retourne un objet pageContent en fonction du type et du type_id
     * @param int $type
     * @param int $typeId
     * @param int $renderBlock
     * @return PageContent
     */
    public function newPageContent(int $type, int $typeId, int $renderBlock): PageContent
    {
        return $this->createPageContent($type, $typeId, $renderBlock);
    }

    /**
     * Créer les pageTranslation en fonctions des langues
     * @return void
     */
    private function createPageTranslation(): void
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
     * @param int $type
     * @param int|null $type_id
     * @param int $renderBlock
     * @return PageContent
     */
    private function createPageContent(
        int  $type = PageConst::CONTENT_TYPE_TEXT,
        ?int $type_id = null,
        int  $renderBlock = 1
    ): PageContent
    {
        if($type === PageConst::CONTENT_TYPE_TEXT)
        {
            $type_id = null;
        }

        $pageContent = new PageContent();
        $pageContent->setType($type);
        $pageContent->setTypeId($type_id);
        $pageContent->setRenderBlock($renderBlock);
        $pageContent->setRenderOrder(1);

        if($type === PageConst::CONTENT_TYPE_TEXT) {
            foreach ($this->locales as $locale) {
                $pageContentTranslation = new PageContentTranslation();
                $pageContentTranslation->setLocale($locale);
                $pageContentTranslation->setText('[' . $locale . '] Contenu de votre page');
                $pageContentTranslation->setPageContent($pageContent);
                $pageContent->addPageContentTranslation($pageContentTranslation);
            }
        }

        if ($this->page !== null) {
            $pageContent->setPage($this->page);
            $this->page->addPageContent($pageContent);
        }

        return $pageContent;
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
    public function getPage(): Page
    {
        return $this->page;
    }
}
