<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Jeu de données pour les pages
 */

namespace App\Tests\Helper\Fixtures\Content;

use App\Entity\Admin\Content\Menu\Menu;
use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\Content\Page\PageContent;
use App\Entity\Admin\Content\Page\PageContentTranslation;
use App\Entity\Admin\Content\Page\PageStatistique;
use App\Entity\Admin\Content\Page\PageTranslation;
use App\Entity\Admin\Content\Tag\Tag;
use App\Entity\Admin\System\User;
use App\Tests\Helper\FakerTrait;
use App\Utils\Content\Comment\CommentConst;
use App\Utils\Content\Page\PageConst;
use App\Utils\Content\Page\PageStatistiqueKey;

trait PageFixturesTrait
{
    use FakerTrait;

    /**
     * Créer une page
     * @param User|null $user
     * @param array $customData
     * @param bool $persist
     * @return Page
     */
    public function createPage(?User $user = null, array $customData = [], bool $persist = true): Page
    {
        if ($user === null) {
            $user = $this->createUserContributeur();
        }

        $data = [
            'user' => $user,
            'render' => PageConst::RENDER_2_2_BLOCK,
            'status' => PageConst::STATUS_PUBLISH,
            'disabled' => self::getFaker()->boolean(),
            'category' => self::getFaker()->randomDigit(),
            'landingPage' => false,
            'isOpenComment' => self::getFaker()->boolean(),
            'nbComment' => self::getFaker()->randomNumber(2),
            'ruleComment' => CommentConst::WAIT_VALIDATION
        ];

        $page = $this->initEntity(Page::class, array_merge($data, $customData));

        if ($persist) {
            $this->persistAndFlush($page);
        }
        return $page;
    }

    /**
     * Création d'une pageTranslation
     * @param Page|null $page
     * @param array $customData
     * @param bool $persist
     * @return PageTranslation
     */
    public function createPageTranslation(?Page $page = null, array $customData = [], bool $persist = true): PageTranslation
    {
        if ($page === null) {
            $page = $this->createPage();
        }

        $data = [
            'page' => $page,
            'locale' => self::getFaker()->locale(),
            'titre' => self::getFaker()->text(),
            'url' => self::getFaker()->url(),
        ];

        $pageTranslation = $this->initEntity(PageTranslation::class, array_merge($data, $customData));
        $pageTranslation->setPage($page);
        $page->addPageTranslation($pageTranslation);

        if ($persist) {
            $this->persistAndFlush($pageTranslation);
        }
        return $pageTranslation;
    }

    /**
     * Création d'un pageContent
     * @param Page|null $page
     * @param array $customData
     * @param bool $persist
     * @return PageContent
     */
    public function createPageContent(?Page $page = null, array $customData = [], bool $persist = true): PageContent
    {
        if ($page === null) {
            $page = $this->createPage();
        }

        $data = [
            'page' => $page,
            'renderBlock' => 1,
            'renderOrder' => 1,
            'type' => PageConst::CONTENT_TYPE_TEXT,
            'typeId' => null,
        ];

        $pageContent = $this->initEntity(PageContent::class, array_merge($data, $customData));
        $pageContent->setPage($page);
        $page->addPageContent($pageContent);

        if ($persist) {
            $this->persistAndFlush($pageContent);
        }
        return $pageContent;
    }

    /**
     * Création d'un PageContentTranslation
     * @param PageContent|null $pageContent
     * @param array $customData
     * @param bool $persist
     * @return PageContentTranslation
     */
    public function createPageContentTranslation(?PageContent $pageContent = null, array $customData = [], bool $persist = true): PageContentTranslation
    {
        if ($pageContent === null) {
            $pageContent = $this->createPageContent();
        }
        $data = [
            'locale' => self::getFaker()->locale(),
            'text' => self::getFaker()->text(),
            'pageContent' => $pageContent,
        ];

        $pageContentTranslation = $this->initEntity(PageContentTranslation::class, array_merge($data, $customData));
        $pageContentTranslation->setPageContent($pageContent);
        $pageContent->addPageContentTranslation($pageContentTranslation);

        if ($persist) {
            $this->persistAndFlush($pageContentTranslation);
        }
        return $pageContentTranslation;
    }

    /**
     * Création d'une page statistique
     * @param Page|null $page
     * @param array $customData
     * @param bool $persist
     * @return PageStatistique
     */
    public function createPageStatistique(?Page $page = null, array $customData = [], bool $persist = true): PageStatistique
    {
        if($page === null) {
            $page = $this->createPage();
        }

        $data = [
            'page' => $page,
            'key' => self::getFaker()->text(),
            'value' => self::getFaker()->text(),
        ];
        $pageStatistique = $this->initEntity(PageStatistique::class, array_merge($data, $customData));
        $pageStatistique->setPage($page);
        $page->addPageStatistique($pageStatistique);

        if ($persist) {
            $this->persistAndFlush($pageStatistique);
        }
        return $pageStatistique;
    }

    /**
     * Création d'un pageMenu
     * @param Page|null $page
     * @param Menu|null $menu
     * @param bool $persist
     * @return Page
     */
    public function createPageMenu(?Page $page = null, ?Menu $menu = null, bool $persist = true): Page
    {
        if ($page === null) {
            $page = $this->createPage();
        }

        if ($menu === null) {
            $menu = $this->createMenu();
        }

        $page->addMenu($menu);
        $menu->addPage($page);

        if ($persist) {
            $this->persistAndFlush($page);
        }
        return $page;
    }

    /**
     * Création d'un pageTag
     * @param Page|null $page
     * @param Tag|null $tag
     * @param bool $persist
     * @return Page
     */
    public function createPageTag(?Page $page = null, ?Tag $tag = null, bool $persist = true): Page
    {
        if ($page === null) {
            $page = $this->createPage();
        }

        if ($tag === null) {
            $tag = $this->createTag();
        }

        $page->addTag($tag);
        $tag->addPage($page);

        if ($persist) {
            $this->persistAndFlush($page);
        }
        return $page;
    }

    /**
     * Création d'un jeu de donnée de page complet
     * @return Page
     */
    public function createPageAllDataDefault() :Page
    {
        $page = $this->createPage(customData: ['render' => PageConst::RENDER_2_BLOCK_BOTTOM]);

        foreach($this->locales as $locale) {
            $this->createPageTranslation($page, ['locale' => $locale]);
        }

        $faq = $this->createFaqAllDataDefault();
        $this->createPageContent($page, ['renderBlock' => 1, 'renderOrder' => 1, 'type' => PageConst::CONTENT_TYPE_FAQ, 'typeId' => $faq->getId()]);
        $pageContent = $this->createPageContent($page, ['renderBlock' => 2, 'renderOrder' => 1]);
        foreach($this->locales as $locale) {
            $this->createPageContentTranslation($pageContent, ['locale' => $locale]);
        }

        $this->createPageTag($page);
        $this->createPageTag($page);

        $this->createPageStatistique($page, ['key' => PageStatistiqueKey::KEY_PAGE_NB_READ, 'value' => self::getFaker()->randomNumber(3)]);
        $this->createPageStatistique($page, ['key' => PageStatistiqueKey::KEY_PAGE_NB_VISITEUR, 'value' => self::getFaker()->randomNumber(3)]);
        return $page;
    }
}