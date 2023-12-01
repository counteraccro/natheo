<?php

namespace App\DataFixtures\Admin\Content\Page;

use App\DataFixtures\AppFixtures;
use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\Content\Page\PageContent;
use App\Entity\Admin\Content\Page\PageContentTranslation;
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
    const PAGE_FIXTURES_DATA_FILE = 'page_fixtures_data.yaml';

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
                            $page->addPageTranslation($this->createPageTranslation($pageTrans));
                        }
                        break;
                    case "pageContent":
                        foreach ($value as $pageCont) {
                            $page->addPageContent($this->createPageContent($pageCont));
                        }
                        break;
                    case "pageStatistique":
                        foreach ($value as $pageStat) {
                            $page->addPageStatistique($this->createPageStatistique($pageStat));
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
     * Set une valeur pour l'objet Page
     * @param string $key
     * @param mixed $value
     * @param mixed $entity
     * @return void
     */
    private function setData(string $key, mixed $value, mixed $entity): void
    {
        $func = 'set' . ucfirst($key);
        $entity->$func($value);
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
     * Créer une nouvelle pageTranslation
     * @param array $data
     * @return PageTranslation
     */
    private function createPageTranslation(array $data): PageTranslation
    {
        $pageTranslation = new PageTranslation();
        foreach ($data as $key => $value) {
            $this->setData($key, $value, $pageTranslation);
        }
        return $pageTranslation;
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
                    $pageContent->addPageContentTranslation($this->createPageContentTranslation($pageContTrans));
                }
            } else {
                $this->setData($key, $value, $pageContent);
            }

        }

        return $pageContent;
    }

    /**
     * Permet de créer un nouveau PageContentTranslation
     * @param array $data
     * @return PageContentTranslation
     */
    private function createPageContentTranslation(array $data): PageContentTranslation
    {
        $pageContentTranslation = new PageContentTranslation();
        foreach ($data as $key => $value) {
            $this->setData($key, $value, $pageContentTranslation);
        }

        return $pageContentTranslation;
    }

    /**
     * Créer une nouvelle pageStatistique
     * @param array $data
     * @return PageStatistique
     */
    private function createPageStatistique(array $data): PageStatistique
    {
        $pageStatistique = new PageStatistique();
        foreach ($data as $key => $value) {
            $this->setData($key, $value, $pageStatistique);
        }
        return $pageStatistique;
    }

    public static function getGroups(): array
    {
        return [self::GROUP_PAGE, self::GROUP_CONTENT];
    }

    public function getOrder(): int
    {
        return 299;
    }
}
