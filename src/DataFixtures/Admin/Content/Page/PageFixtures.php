<?php
/**
 * @author Gourdon Aymeric
 * @version 1.2
 * Fixtures pour générer des pages
 */
namespace App\DataFixtures\Admin\Content\Page;

use App\DataFixtures\AppFixtures;
use App\Entity\Admin\Content\Faq\Faq;
use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\Content\Page\PageContent;
use App\Entity\Admin\Content\Page\PageContentTranslation;
use App\Entity\Admin\Content\Page\PageMeta;
use App\Entity\Admin\Content\Page\PageMetaTranslation;
use App\Entity\Admin\Content\Page\PageStatistique;
use App\Entity\Admin\Content\Page\PageTranslation;
use App\Entity\Admin\Content\Tag\Tag;
use App\Entity\Admin\System\User;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

class PageFixtures extends AppFixtures implements FixtureGroupInterface, OrderedFixtureInterface
{
    const PAGE_FIXTURES_DATA_FILE = 'content' . DIRECTORY_SEPARATOR . 'page' . DIRECTORY_SEPARATOR .
    'page_fixtures_data.yaml';

    public function load(ObjectManager $manager): void
    {
        $data = Yaml::parseFile($this->pathDataFixtures . self::PAGE_FIXTURES_DATA_FILE);

        foreach ($data['pages'] as $ref => $data) {

            $page = new Page();
            foreach ($data as $key => $value) {
                switch ($key) {
                    case "user" :
                        $page->setUser($this->getReference($value, User::class));
                        break;
                    case "tags" :
                        $this->addTag($page, $value);
                        break;
                    case "pageTranslation":
                        foreach ($value as $pageTrans) {
                            $page->addPageTranslation($this->populateEntity($pageTrans, new PageTranslation()));
                        }
                        break;
                    case "pageContent":
                        foreach ($value as $pageCont) {
                            $page->addPageContent($this->createPageContent($pageCont));
                        }
                        break;
                    case "pageStatistique":
                        foreach ($value as $pageStat) {
                            $page->addPageStatistique($this->populateEntity($pageStat, new PageStatistique()));
                        }
                        break;
                    case "pageMeta":
                        foreach ($value as $pageMet) {
                            $page->addPageMeta($this->createPageMeta($pageMet));
                        }
                        break;
                    default:
                        $this->setData($key, $value, $page);
                }
            }
            $manager->persist($page);
            $this->addReference($ref, $page);
        }
        $manager->flush();
    }

    /**
     * Ajoute des tags à une page
     * @param Page $page
     * @param array $tabTags
     * @return void
     */
    private function addTag(Page $page, array $tabTags): void
    {
        foreach ($tabTags as $tag) {
            $page->addTag($this->getReference($tag, Tag::class));
        }
    }

    /**
     * Permet de créer un nouveau PageContent
     * @param array $data
     * @return PageContent
     */
    private function createPageContent(array $data): PageContent
    {
        $pageContent = new PageContent();
        foreach ($data as $key => $value) {

            if ($key === 'pageContentTranslation') {
                foreach ($value as $pageContTrans) {
                    $pageContent->addPageContentTranslation($this->populateEntity(
                        $pageContTrans, new PageContentTranslation()));
                }
            } elseif ($key === 'typeId') {
                // Possible que la référence soit un id en dur
                if ($this->hasReference($value, Faq::class)) {
                    $pageContent->setTypeId($this->getReference($value, Faq::class)->getId());
                }else {
                    $pageContent->setTypeId($value);
                }

            } else {
                $this->setData($key, $value, $pageContent);
            }
        }

        return $pageContent;
    }

    /**
     * Permet de créer un nouveau pageMeta
     * @param array $data
     * @return PageMeta
     */
    private function createPageMeta(array $data): PageMeta {
        $pageMeta = new PageMeta();
        foreach ($data as $key => $value) {
            if ($key === 'pageMetaTranslation') {
                foreach ($value as $pageMetaTrans) {
                    $pageMeta->addPageMetaTranslation($this->populateEntity($pageMetaTrans, new PageMetaTranslation()));
                }
            }
            else {
                $this->setData($key, $value, $pageMeta);
            }
        }
        return $pageMeta;
    }

    public static function getGroups(): array
    {
        return [self::GROUP_PAGE, self::GROUP_CONTENT];
    }

    public function getOrder(): int
    {
        return 290;
    }
}
