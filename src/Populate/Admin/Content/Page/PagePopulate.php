<?php

declare(strict_types=1);
/**
 * Permet de merger les données venant d'un tableau à un objet page
 * @author Gourdon Aymeric
 * @version 2.0
 */

namespace App\Populate\Admin\Content\Page;

use App\Entity\Admin\Content\Faq\Faq;
use App\Entity\Admin\Content\Menu\Menu;
use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\Content\Page\PageContent;
use App\Entity\Admin\Content\Page\PageContentTranslation;
use App\Entity\Admin\Content\Page\PageMeta;
use App\Entity\Admin\Content\Page\PageMetaTranslation;
use App\Entity\Admin\Content\Tag\Tag;
use App\Entity\Trait\PopulateTrait;
use App\Enum\Admin\Comment\CommentStatus;
use App\Enum\Admin\Content\Page\PageCategory;
use App\Enum\Admin\Content\Page\PageContentType;
use App\Enum\Admin\Content\Page\PageRender;
use App\Enum\Admin\Content\Page\PageStatus;
use App\Service\Admin\Content\Page\PageService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class PagePopulate
{
    use PopulateTrait;

    /**
     * Champs modifiables de PageTranslation
     * @var string[]
     */
    private const array FIELDS_PAGE_TRANSLATION = ['titre', 'url'];

    /**
     * Champs modifiables de PageContent
     * @var string[]
     */
    private const array FIELDS_PAGE_CONTENT = ['renderBlock', 'renderOrder'];

    /**
     * Champs modifiables de PageContentTranslation
     * @var string[]
     */
    private const array FIELDS_PAGE_CONTENT_TRANSLATION = ['locale', 'text'];

    /**
     * Champs modifiables de PageMeta
     * @var string[]
     */
    private const array FIELDS_PAGE_META = ['name'];

    /**
     * Champs modifiables de PageMetaTranslation
     * @var string[]
     */
    private const array FIELDS_PAGE_META_TRANSLATION = ['locale', 'value'];

    /**
     * Clé pageTranslations
     * @var string
     */
    const string KEY_PAGE_TRANSLATION = 'pageTranslations';

    /**
     * Clé tags
     * @var string
     */
    const string KEY_TAGS = 'tags';

    /**
     * Clé menus
     */
    const string KEY_MENUS = 'menus';

    /**
     * Clé pageContents
     * @var string
     */
    const string KEY_PAGE_CONTENT = 'pageContents';

    /**
     * Clé pageMeta
     * @var string
     */
    const string KEY_PAGE_META = 'pageMetas';

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
     * Champs dont la valeur reçue est invalide
     * @var string[]
     */
    private array $errors = [];

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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function populate(): static
    {
        $this->populatePage();
        $this->populatePageTranslation();
        $this->populatePageContent();
        $this->populateTags();
        $this->populateMenus();
        $this->populatePageMeta();
        return $this;
    }

    /**
     * Met à jour certaines données de l'objet Page
     * @return void
     */
    private function populatePage(): void
    {
        $render = PageRender::tryFrom($this->getInt('render'));
        $status = PageStatus::tryFrom($this->getInt('status'));
        $category = PageCategory::tryFrom($this->getInt('category'));
        $ruleComment = CommentStatus::tryFrom($this->getInt('ruleComment'));

        foreach (
            ['render' => $render, 'status' => $status, 'category' => $category, 'ruleComment' => $ruleComment]
            as $field => $value
        ) {
            if ($value === null) {
                $this->errors[] = $field;
            }
        }

        if ($render !== null) {
            $this->page->setRender($render->value);
        }
        if ($status !== null) {
            $this->page->setStatus($status->value);
        }
        if ($category !== null) {
            $this->page->setCategory($category->value);
        }
        if ($ruleComment !== null) {
            $this->page->setRuleComment($ruleComment->value);
        }

        $this->page->setLandingPage(boolval($this->populate['landingPage'] ?? false));
        $this->page->setIsOpenComment(
            boolval($this->populate['isOpenComment'] ?? ($this->populate['openComment'] ?? false)),
        );
        $headerImg = $this->populate['headerImg'] ?? null;
        $this->page->setHeaderImg(is_string($headerImg) && $headerImg !== '' ? $headerImg : null);
    }

    /**
     * Retourne une valeur entière de $populate, 0 si absente ou non numérique
     * @param string $key
     * @return int
     */
    private function getInt(string $key): int
    {
        $value = $this->populate[$key] ?? null;
        return is_numeric($value) ? (int) $value : 0;
    }

    /**
     * Retourne les champs dont la valeur reçue est invalide (vide si tout est valide)
     * @return string[]
     */
    public function getErrors(): array
    {
        return array_values(array_unique($this->errors));
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
                        $pageTranslation = $this->mergeData(
                            $pageTranslation,
                            $dataTranslation,
                            self::FIELDS_PAGE_TRANSLATION,
                        );
                    }
                }
            }
        }
    }

    /**
     * Reset les tags présents dans $page et ajoute à la place les tags
     * associés à la page avec $populate si la clé 'tags' est présente
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function populateTags(): void
    {
        if (isset($this->populate[self::KEY_TAGS])) {
            $this->page->getTags()->clear();
            foreach ($this->populate[self::KEY_TAGS] as $dataTag) {
                $id = is_array($dataTag) ? $dataTag['id'] ?? null : null;
                $tag = is_numeric($id) ? $this->pageService->findOneById(Tag::class, (int) $id) : null;
                if ($tag !== null) {
                    $this->page->addTag($tag);
                }
            }
        }
    }

    /**
     * Reset les menus présents dans $page et ajoute à la place les menus associés à la page avec $populate si
     * la clé "menu" est présente
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function populateMenus(): void
    {
        if (isset($this->populate[self::KEY_MENUS])) {
            $this->page->getMenus()->clear();
            if (!in_array('-1', $this->populate[self::KEY_MENUS])) {
                foreach ($this->populate[self::KEY_MENUS] as $dataMenu) {
                    $menu = is_numeric($dataMenu)
                        ? $this->pageService->findOneById(Menu::class, (int) $dataMenu)
                        : null;
                    if ($menu !== null) {
                        $this->page->addMenu($menu);
                    }
                }
            }
        }
    }

    /**
     * Reset les pageContent dans $page et ajoute à la place les pageContent présent dans
     * $populate si la clé 'pageContents' est présente
     * @return void
     */
    private function populatePageContent(): void
    {
        if (isset($this->populate[self::KEY_PAGE_CONTENT])) {
            $this->page->getPageContents()->clear();

            $nbContent = $this->getNbContentByRender($this->page->getRender() ?? 0);

            for ($i = 1; $i <= $nbContent; $i++) {
                foreach ($this->populate[self::KEY_PAGE_CONTENT] as $content) {
                    if ($content['renderBlock'] === $i) {
                        $type = PageContentType::tryFrom(
                            is_numeric($content['type'] ?? null) ? (int) $content['type'] : 0,
                        );
                        if ($type === null || !$this->isValidContentTypeId($type, $content['typeId'] ?? null)) {
                            $this->errors[] = 'pageContents';
                            continue;
                        }

                        $pageContent = new PageContent();
                        /** @var PageContent $pageContent */
                        $pageContent = $this->mergeData($pageContent, $content, self::FIELDS_PAGE_CONTENT);
                        $pageContent->setType($type->value);

                        switch ($type) {
                            case PageContentType::TEXT:
                                foreach ($content['pageContentTranslations'] ?? [] as $pageContentT) {
                                    $pageContentTranslation = new PageContentTranslation();
                                    /** @var PageContentTranslation $pageContentTranslation */
                                    $pageContentTranslation = $this->mergeData(
                                        $pageContentTranslation,
                                        $pageContentT,
                                        self::FIELDS_PAGE_CONTENT_TRANSLATION,
                                    );
                                    $pageContentTranslation->setPageContent($pageContent);
                                    $pageContent->addPageContentTranslation($pageContentTranslation);
                                }
                                break;
                            default:
                                $pageContent->setTypeId((int) $content['typeId']);
                        }
                        $pageContent->setPage($this->page);
                        $this->page->addPageContent($pageContent);
                    }
                }
            }
        }
    }

    /**
     * Vérifie que le contenu référencé existe : FAQ existante, catégorie de listing valide
     * @param PageContentType $type
     * @param mixed $typeId
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function isValidContentTypeId(PageContentType $type, mixed $typeId): bool
    {
        return match ($type) {
            PageContentType::TEXT => true,
            PageContentType::FAQ => is_numeric($typeId) &&
                $this->pageService->findOneById(Faq::class, (int) $typeId) !== null,
            PageContentType::LISTING => is_numeric($typeId) && PageCategory::tryFrom((int) $typeId) !== null,
        };
    }

    /**
     * Reset les pageMeta dans $page et ajoute à la place les pageMeta présents dans $populate
     * @return void
     */
    private function populatePageMeta(): void
    {
        if (isset($this->populate[self::KEY_PAGE_META])) {
            $this->page->getPageMetas()->clear();
            foreach ($this->populate[self::KEY_PAGE_META] as $dataMeta) {
                $pageMeta = new PageMeta();
                $pageMeta = $this->mergeData($pageMeta, $dataMeta, self::FIELDS_PAGE_META);

                foreach ($dataMeta['pageMetaTranslations'] as $dataPageMetaTranslation) {
                    $pageMetaTranslation = new PageMetaTranslation();
                    $pageMetaTranslation = $this->mergeData(
                        $pageMetaTranslation,
                        $dataPageMetaTranslation,
                        self::FIELDS_PAGE_META_TRANSLATION,
                    );
                    $pageMeta->addPageMetaTranslation($pageMetaTranslation);
                }
                $this->page->addPageMeta($pageMeta);
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
     * Retourne le nombre de PageContent en fonction de render
     * @param int $render
     * @return int
     */
    private function getNbContentByRender(int $render): int
    {
        return match ($render) {
            PageRender::ONE_BLOCK->value => 1,
            PageRender::TWO_BLOCK->value, PageRender::TWO_BLOCK_BOTTOM->value => 2,
            PageRender::THREE_BLOCK_BOTTOM->value,
            PageRender::ONE_TWO_BLOCK->value,
            PageRender::TWO_ONE_BLOCK->value,
            PageRender::THREE_BLOCK->value
                => 3,
            PageRender::TWO_TWO_BLOCK->value => 4,
            default => 0,
        };
    }
}
