<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Fixtures pour générer des pages
 */
namespace App\DataFixtures\Admin\Content\Page;

use App\DataFixtures\AppFixtures;
use App\Entity\Admin\Content\Faq\Faq;
use App\Entity\Admin\Content\Media\Media;
use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\Content\Page\PageContent;
use App\Entity\Admin\Content\Page\PageContentTranslation;
use App\Entity\Admin\Content\Page\PageMeta;
use App\Entity\Admin\Content\Page\PageMetaTranslation;
use App\Entity\Admin\Content\Page\PageStatistique;
use App\Entity\Admin\Content\Page\PageTranslation;
use App\Entity\Admin\Content\Tag\Tag;
use App\Entity\Admin\System\User;
use App\Enum\Admin\Comment\CommentStatus;
use App\Enum\Admin\Content\Page\PageCategory;
use App\Enum\Admin\Content\Page\PageContentType;
use App\Enum\Admin\Content\Page\PageRender;
use App\Enum\Admin\Content\Page\PageStatistics;
use App\Enum\Admin\Content\Page\PageStatus;
use App\Enum\Admin\Content\Page\PageValidationStatus;
use App\Service\Admin\Content\Page\PageHistory;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Yaml\Yaml;

class PageFixtures extends AppFixtures implements FixtureGroupInterface, OrderedFixtureInterface
{
    /**
     * Dossier des pages : un fichier YAML par page, textes des blocs dans <page>/<bloc>.<locale>.md
     */
    const PAGE_FIXTURES_FOLDER = 'content' . DIRECTORY_SEPARATOR . 'page' . DIRECTORY_SEPARATOR;

    /**
     * Valeurs communes à toutes les pages
     */
    const PAGE_DEFAULTS_FILE = '_defaults.yaml';

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function load(ObjectManager $manager): void
    {
        $pageHistory = new PageHistory($this->container, new User());
        $pageHistory->removePageHistoryFolder();

        $folder = $this->pathDataFixtures . self::PAGE_FIXTURES_FOLDER;
        $defaults = Yaml::parseFile($folder . self::PAGE_DEFAULTS_FILE);

        foreach ($this->loadYamlFolder($folder) as $ref => $file) {
            $page = $this->createPage($ref, $file['data'], $file['dir'], $defaults);
            $manager->persist($page);
            $this->addReference($ref, $page);
        }
        $manager->flush();
    }

    /**
     * Crée la page $ref
     * @param string $ref
     * @param array $data
     * @param string $dir dossier du fichier YAML de la page
     * @param array $defaults
     * @return Page
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function createPage(string $ref, array $data, string $dir, array $defaults): Page
    {
        $page = new Page();
        $page->setUser($this->getUserReference($data['user'] ?? 'User'));
        $page->setLandingPage(false);

        foreach ($data as $key => $value) {
            switch ($key) {
                case 'user':
                case 'menus':
                case 'metas':
                case 'date':
                    break;
                case 'render':
                    $page->setRender($this->enumValue(PageRender::class, $value));
                    break;
                case 'category':
                    $page->setCategory($this->enumValue(PageCategory::class, $value));
                    break;
                case 'status':
                    $page->setStatus($this->enumValue(PageStatus::class, $value));
                    break;
                case 'ruleComment':
                    $page->setRuleComment($this->enumValue(CommentStatus::class, $value));
                    break;
                case 'validationStatus':
                    $page->setValidationStatus($this->enumValue(PageValidationStatus::class, $value));
                    break;
                case 'validationUser':
                    $page->setValidationUser($this->getUserReference($value));
                    break;
                case 'validationAt':
                    $page->setValidationAt(new \DateTime($value));
                    break;
                case 'tags':
                    foreach ($value as $tag) {
                        $page->addTag($this->getReference($tag, Tag::class));
                    }
                    break;
                case 'headerImg':
                    $page->setHeaderImg($this->getMediaPath($value));
                    break;
                case 'translations':
                    foreach ($value as $locale => $translation) {
                        $page->addPageTranslation(
                            $this->populateEntity(['locale' => $locale] + $translation, new PageTranslation()),
                        );
                    }
                    break;
                case 'blocks':
                    foreach ($value as $block) {
                        $page->addPageContent(
                            $this->createPageContent(
                                $block,
                                $dir . DIRECTORY_SEPARATOR . $ref,
                                array_keys($data['translations']),
                            ),
                        );
                    }
                    break;
                case 'stats':
                    $page->addPageStatistique(
                        $this->createStatistique(PageStatistics::NB_VISITEUR, $value['visitors']),
                    );
                    $page->addPageStatistique($this->createStatistique(PageStatistics::NB_READ, $value['reads']));
                    break;
                default:
                    $this->setData($key, $value, $page);
            }
        }

        foreach (array_merge($defaults['metas'], $data['metas'] ?? []) as $name => $values) {
            $page->addPageMeta($this->createPageMeta($name, $values));
        }

        return $page;
    }

    /**
     * Retourne le chemin public du média $ref, sans l'hôte, pour que la démo s'affiche quelle que soit l'adresse du site
     * @param string $ref
     * @return string
     */
    private function getMediaPath(string $ref): string
    {
        $webPath = (string) $this->getReference($ref, Media::class)->getWebPath();
        return (string) parse_url($webPath, PHP_URL_PATH);
    }

    /**
     * Crée un bloc de page. Un bloc texte lit son contenu dans $textDir/<fichier>.<locale>.md, avec ses {{media:<ref>}} remplacés,
     * où <fichier> vaut « file » ou, par défaut, le numéro du bloc (suivi de « -<order> » au-delà du premier)
     * @param array $data
     * @param string $textDir
     * @param string[] $locales
     * @return PageContent
     */
    private function createPageContent(array $data, string $textDir, array $locales): PageContent
    {
        $order = $data['order'] ?? 1;
        $type = $this->enumCase(PageContentType::class, $data['type']);

        $pageContent = new PageContent();
        $pageContent->setRenderBlock($data['block']);
        $pageContent->setRenderOrder($order);
        $pageContent->setType($type->value);

        switch ($type) {
            case PageContentType::FAQ:
                $pageContent->setTypeId($this->getReference($data['faq'], Faq::class)->getId());
                break;
            case PageContentType::LISTING:
                $pageContent->setTypeId($this->enumValue(PageCategory::class, $data['listing']));
                break;
            case PageContentType::TEXT:
                $fileName = $data['file'] ?? $data['block'] . ($order > 1 ? '-' . $order : '');
                foreach ($locales as $locale) {
                    $path = $textDir . DIRECTORY_SEPARATOR . $fileName . '.' . $locale . '.md';
                    if (!is_file($path)) {
                        throw new \RuntimeException(sprintf('Texte du bloc introuvable : %s', $path));
                    }
                    $translation = new PageContentTranslation();
                    $translation->setLocale($locale);
                    $translation->setText($this->replaceMediaTags(file_get_contents($path)));
                    $pageContent->addPageContentTranslation($translation);
                }
                break;
        }

        return $pageContent;
    }

    /**
     * Remplace chaque {{media:<ref>}} de $text par le chemin public du média <ref>
     * @param string $text
     * @return string
     * @throws \RuntimeException si <ref> n'est pas une référence de média valide
     */
    private function replaceMediaTags(string $text): string
    {
        return preg_replace_callback(
            '/\{\{media:([^}]*)\}\}/',
            function (array $match): string {
                if (!preg_match('/^[a-z0-9-]+$/', $match[1]) || !$this->hasReference($match[1], Media::class)) {
                    throw new \RuntimeException(sprintf('Média inconnu dans un texte de bloc : %s', $match[0]));
                }
                return $this->getMediaPath($match[1]);
            },
            $text,
        );
    }

    /**
     * Crée une statistique de page
     * @param PageStatistics $key
     * @param int $value
     * @return PageStatistique
     */
    private function createStatistique(PageStatistics $key, int $value): PageStatistique
    {
        $statistique = new PageStatistique();
        $statistique->setKey($key->value);
        $statistique->setValue((string) $value);
        return $statistique;
    }

    /**
     * Crée la meta $name, traduite avec $values (locale => valeur)
     * @param string $name
     * @param array $values
     * @return PageMeta
     */
    private function createPageMeta(string $name, array $values): PageMeta
    {
        $pageMeta = new PageMeta();
        $pageMeta->setName($name);
        foreach ($values as $locale => $value) {
            $translation = new PageMetaTranslation();
            $translation->setLocale($locale);
            $translation->setValue($value);
            $pageMeta->addPageMetaTranslation($translation);
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
