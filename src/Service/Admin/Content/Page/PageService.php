<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Service gérant la création de page
 */

namespace App\Service\Admin\Content\Page;

use App\Entity\Admin\Content\Faq\Faq;
use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\Content\Page\PageTranslation;
use App\Entity\Admin\System\User;
use App\Enum\Admin\Content\Page\PageCategory;
use App\Enum\Admin\Content\Page\PageCsrfToken;
use App\Enum\Admin\Content\Page\PageContentType;
use App\Enum\Admin\Content\Page\PageRender;
use App\Enum\Admin\Content\Page\PageStatistics;
use App\Enum\Admin\Content\Page\PageStatus;
use App\Enum\Admin\System\Options\OptionSystem;
use App\Repository\Admin\Content\Page\PageRepository;
use App\Repository\Admin\Content\Page\PageTranslationRepository;
use App\Security\Voter\ContentVoter;
use App\Service\Admin\AppAdminService;
use App\Service\Admin\GridService;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class PageService extends AppAdminService
{
    /**
     * Retourne une liste de page paginé
     * @param int $page
     * @param int $limit
     * @param array $queryParams
     * @param int|null $userId
     * @return Paginator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllPaginate(int $page, int $limit, array $queryParams, ?int $userId = null): Paginator
    {
        $repo = $this->getRepository(Page::class);
        return $repo->getAllPaginate($page, $limit, $queryParams, $userId);
    }

    /**
     * Construit le tableau de donnée à envoyé au tableau GRID
     * @param int $page
     * @param int $limit
     * @param array $queryParams
     * @param int|null $userId
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllFormatToGrid(int $page, int $limit, array $queryParams, ?int $userId = null): array
    {
        $translator = $this->getTranslator();
        $gridService = $this->getGridService();
        $locale = $this->getRequestStack()->getCurrentRequest()->getLocale();
        $csrfTokenManager = $this->getCsrfTokenManager();
        $csrfTokens = [];
        foreach ([PageCsrfToken::DISABLED, PageCsrfToken::DELETE, PageCsrfToken::LANDING_PAGE] as $csrfToken) {
            $csrfTokens[$csrfToken->value] = $csrfTokenManager->getToken($csrfToken->value)->getValue();
        }

        $column = [
            $translator->trans('page.grid.id', domain: 'page'),
            $translator->trans('page.grid.title', domain: 'page'),
            $translator->trans('page.grid.status', domain: 'page'),
            $translator->trans('page.grid.tag', domain: 'page'),
            $translator->trans('page.grid.comment', domain: 'page'),
            $translator->trans('page.grid.nb_see', domain: 'page'),
            $translator->trans('page.grid.update_at', domain: 'page'),
            $translator->trans('page.grid.author', domain: 'page'),
            GridService::KEY_ACTION,
        ];

        $dataPaginate = $this->getAllPaginate($page, $limit, $queryParams, $userId);

        $nb = $dataPaginate->count();
        $data = [];
        foreach ($dataPaginate as $element) {
            /* @var Page $element */

            $action = $this->generateTabAction($element, $locale, $csrfTokens);

            $isDisabled = '';
            $isLandingPage = '';

            if ($element->isLandingPage()) {
                $isLandingPage = '<svg class="float-start w-5 h-5 text-(--success)" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                         viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-width="2"
                              d="M5 9a7 7 0 1 1 8 6.93V21a1 1 0 1 1-2 0v-5.07A7.001 7.001 0 0 1 5 9Zm5.94-1.06A1.5 1.5 0 0 1 12 7.5a1 1 0 1 0 0-2A3.5 3.5 0 0 0 8.5 9a1 1 0 0 0 2 0c0-.398.158-.78.44-1.06Z"></path>
                    </svg>';
            }

            $data[] = [
                $translator->trans('page.grid.id', domain: 'page') =>
                    $element->getId() . ' ' . $isDisabled . ' ' . $isLandingPage,
                $translator->trans('page.grid.title', domain: 'page') => $this->getEscapedTitle($element, $locale),
                $translator->trans('page.grid.status', domain: 'page') => $this->getStatusStr($element->getStatus()),
                $translator->trans('page.grid.tag', domain: 'page') => $this->getTags($element->getTags()),
                $translator->trans('page.grid.comment', domain: 'page') => $element->getNbComment(),
                $translator->trans('page.grid.nb_see', domain: 'page') =>
                    $element->getPageStatistiqueByKey(PageStatistics::NB_READ->value)?->getValue() ?? 0,
                $translator->trans('page.grid.update_at', domain: 'page') => $element
                    ->getUpdateAt()
                    ->format('d/m/y H:i'),
                $translator->trans('page.grid.author', domain: 'page') => htmlspecialchars(
                    (string) $element->getUser()?->getLogin(),
                    ENT_QUOTES,
                ),
                GridService::KEY_ACTION => $action,
                'isDisabled' => $element->isDisabled(),
            ];
        }

        $tabReturn = [
            GridService::KEY_NB => $nb,
            GridService::KEY_DATA => $data,
            GridService::KEY_COLUMN => $column,
            GridService::KEY_RAW_SQL => $gridService->getFormatedSQLQuery($dataPaginate),
            GridService::KEY_LIST_ORDER_FIELD => [
                'id' => $translator->trans('page.grid.id', domain: 'page'),
                'page_translation.titre' => $translator->trans('page.grid.title', domain: 'page'),
                'status' => $translator->trans('page.grid.status', domain: 'page'),
                'updateAt' => $translator->trans('page.grid.update_at', domain: 'page'),
            ],
        ];
        return $gridService->addAllDataRequiredGrid($tabReturn);
    }

    /**
     * Génère le tableau d'actions de la grille des pages, selon les droits (ContentVoter)
     * @param Page $page
     * @param string $locale
     * @param array<string, string> $csrfTokens jetons CSRF indexés par identifiant PageCsrfToken
     * @return array[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function generateTabAction(Page $page, string $locale, array $csrfTokens): array
    {
        $translator = $this->getTranslator();
        $router = $this->getRouter();
        $security = $this->getSecurity();

        $label = $this->getEscapedTitle($page, $locale);
        $actions = [];

        // La landing page ne peut être ni désactivée ni supprimée
        if (!$page->isLandingPage() && $security->isGranted(ContentVoter::DISABLE, $page)) {
            $actionDisabled = [
                'label' => [
                    'M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z',
                    'M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
                ],
                'color' => 'primary',
                'type' => 'put',
                'url' => $router->generate('admin_page_update_disabled', ['id' => $page->getId()]),
                'ajax' => true,
                'csrf' => $csrfTokens[PageCsrfToken::DISABLED->value],
            ];
            if (!$page->isDisabled()) {
                $actionDisabled['label'] = [
                    'M3.933 13.909A4.357 4.357 0 0 1 3 12c0-1 4-6 9-6m7.6 3.8A5.068 5.068 0 0 1 21 12c0 1-3 6-9 6-.314 0-.62-.014-.918-.04M5 19 19 5m-4 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
                ];
                $actionDisabled['confirm'] = true;
                $actionDisabled['msgConfirm'] = $translator->trans(
                    'page.confirm.disabled.msg',
                    ['label' => $label],
                    'page',
                );
            }
            $actions[] = $actionDisabled;
        }

        if (!$page->isLandingPage() && $security->isGranted(ContentVoter::DELETE, $page)) {
            $actions[] = [
                'label' => [
                    'M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z',
                ],
                'color' => 'danger',
                'type' => 'delete',
                'url' => $router->generate('admin_page_delete', ['id' => $page->getId()]),
                'ajax' => true,
                'confirm' => true,
                'msgConfirm' => $translator->trans('page.confirm.delete.msg', ['label' => $label], 'page'),
                'csrf' => $csrfTokens[PageCsrfToken::DELETE->value],
            ];
        }

        if (!$security->isGranted(ContentVoter::EDIT, $page)) {
            return $actions;
        }

        $actions[] = [
            'label' => [
                'M10.779 17.779 4.36 19.918 6.5 13.5m4.279 4.279 8.364-8.643a3.027 3.027 0 0 0-2.14-5.165 3.03 3.03 0 0 0-2.14.886L6.5 13.5m4.279 4.279L6.499 13.5m2.14 2.14 6.213-6.504M12.75 7.04 17 11.28',
            ],
            'color' => 'primary',
            'id' => $page->getId(),
            'url' => $router->generate('admin_page_update', ['id' => $page->getId()]),
            'ajax' => false,
        ];

        if ($this->canBeLandingPage($page) && !$page->isLandingPage()) {
            $actions[] = [
                'label' => [
                    'M5 9a7 7 0 1 1 8 6.93V21a1 1 0 1 1-2 0v-5.07A7.001 7.001 0 0 1 5 9Zm5.94-1.06A1.5 1.5 0 0 1 12 7.5a1 1 0 1 0 0-2A3.5 3.5 0 0 0 8.5 9a1 1 0 0 0 2 0c0-.398.158-.78.44-1.06Z',
                ],
                'color' => 'success',
                'url' => $router->generate('admin_page_switch_Landing_page', ['id' => $page->getId()]),
                'type' => 'put',
                'ajax' => true,
                'confirm' => true,
                'msgConfirm' => $translator->trans('page.confirm.landing.page.msg', ['label' => $label], 'page'),
                'csrf' => $csrfTokens[PageCsrfToken::LANDING_PAGE->value],
            ];
        }

        return $actions;
    }

    /**
     * Une landing page doit être publiée et active
     * @param Page $page
     * @return bool
     */
    public function canBeLandingPage(Page $page): bool
    {
        return $page->getStatus() === PageStatus::PUBLISH->value && !$page->isDisabled();
    }

    /**
     * Retourne le titre de la page dans la locale, échappé pour un affichage HTML
     * @param Page $page
     * @param string $locale
     * @return string
     */
    public function getEscapedTitle(Page $page, string $locale): string
    {
        return htmlspecialchars((string) $page->getPageTranslationByLocale($locale)?->getTitre(), ENT_QUOTES);
    }

    /**
     * Retourne le status sous forme d'un label traduit
     * @param int $status
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getStatusStr(int $status): string
    {
        $translator = $this->getTranslator();

        return match ($status) {
            PageStatus::DRAFT->value => $translator->trans('page.status.draft', domain: 'page'),
            PageStatus::PUBLISH->value => $translator->trans('page.status.publish', domain: 'page'),
            PageStatus::ARCHIVED->value => $translator->trans('page.status.archived', domain: 'page'),
            default => $translator->trans('page.status.inconnu', domain: 'page'),
        };
    }

    /**
     * Retourne la liste de status que peut avoir une page
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllStatus(): array
    {
        $translator = $this->getTranslator();

        return [
            PageStatus::DRAFT->value => $translator->trans('page.status.draft', domain: 'page'),
            PageStatus::PUBLISH->value => $translator->trans('page.status.publish', domain: 'page'),
            PageStatus::ARCHIVED->value => $translator->trans('page.status.archived', domain: 'page'),
        ];
    }

    /**
     * Retourne la liste de rendu que peut avoir une page
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllRender(): array
    {
        $translator = $this->getTranslator();

        return [
            PageRender::ONE_BLOCK->value => $translator->trans('page.render.1.block', domain: 'page'),
            PageRender::TWO_BLOCK->value => $translator->trans('page.render.2.block', domain: 'page'),
            PageRender::THREE_BLOCK->value => $translator->trans('page.render.3.block', domain: 'page'),
            PageRender::TWO_BLOCK_BOTTOM->value => $translator->trans('page.render.2.block.bottom', domain: 'page'),
            PageRender::THREE_BLOCK_BOTTOM->value => $translator->trans('page.render.3.block.bottom', domain: 'page'),
            PageRender::ONE_TWO_BLOCK->value => $translator->trans('page.render.1.2.block', domain: 'page'),
            PageRender::TWO_ONE_BLOCK->value => $translator->trans('page.render.2.1.block', domain: 'page'),
            PageRender::TWO_TWO_BLOCK->value => $translator->trans('page.render.2.2.block', domain: 'page'),
        ];
    }

    /**
     * Retourne la liste de choix de type de content
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllContent(): array
    {
        $translator = $this->getTranslator();

        return [
            PageContentType::TEXT->value => $translator->trans('page.content.text', domain: 'page'),
            PageContentType::FAQ->value => $translator->trans('page.content.type.faq', domain: 'page'),
            PageContentType::LISTING->value => $translator->trans('page.content.type.listing', domain: 'page'),
        ];
    }

    /**
     * Retourne la liste des catégories
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllCategories(): array
    {
        $translator = $this->getTranslator();
        return [
            PageCategory::PAGE->value => $translator->trans('page.category.page', domain: 'page'),
            PageCategory::PROJET->value => $translator->trans('page.category.projet', domain: 'page'),
            PageCategory::ARTICLE->value => $translator->trans('page.category.article', domain: 'page'),
            PageCategory::BLOG->value => $translator->trans('page.category.blog', domain: 'page'),
            PageCategory::EVENEMENT->value => $translator->trans('page.category.evenement', domain: 'page'),
            PageCategory::NEWS->value => $translator->trans('page.category.news', domain: 'page'),
            PageCategory::EVOLUTION->value => $translator->trans('page.category.evolution', domain: 'page'),
            PageCategory::DOCUMENTATION->value => $translator->trans('page.category.documentation', domain: 'page'),
            PageCategory::FAQ->value => $translator->trans('page.category.faq', domain: 'page'),
        ];
    }

    /**
     * Retourne une catégorie en fonction de son id
     * @param int $id
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getCategoryById(int $id): string
    {
        $categories = $this->getAllCategories();
        if (isset($categories[$id])) {
            return $categories[$id];
        }
        return '';
    }

    /**
     * Génère une liste de tags au format HTML
     * @param Collection $tags
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getTags(Collection $tags): string
    {
        $requestStack = $this->getRequestStack();

        $locale = $requestStack->getCurrentRequest()->getLocale();
        $tagRender = $this->getTagRender();
        $return = '';
        foreach ($tags as $tag) {
            $return .= ' ' . $tagRender->getHtml($tag, $locale);
        }
        return trim($return);
    }

    /**
     * Permet de détecter si la date du dernier history enregistré est supérieur à la date
     * d'update de la page.
     * Retourne un tableau avec un message si la date du dernier history est supérieur
     * @param Page $page
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getDiffBetweenHistoryAndPage(Page $page): array
    {
        $security = $this->getSecurity();
        $containerBag = $this->getContainerBag();
        $translator = $this->getTranslator();

        $return = [
            'show_msg' => false,
            'id' => 0,
            'msg' => '',
        ];

        /** @var User $user */
        $user = $security->getUser();
        $pageHistory = new PageHistory($containerBag, $user);
        $history = $pageHistory->getHistory($page->getId());
        if (empty($history)) {
            return $return;
        }

        if ($page->getUpdateAt() === null || $history[0]['time'] > $page->getUpdateAt()->getTimestamp()) {
            $msg = $translator->trans('page.msg.history.reload', domain: 'page');
            if ($page->getUpdateAt() === null) {
                $msg = $translator->trans('page.msg.history.new.reload', domain: 'page');
            }

            $return = [
                'show_msg' => true,
                'id' => $history[0]['id'],
                'msg' => $msg,
            ];
        }
        return $return;
    }

    /**
     * Vérifie qu'aucune autre page n'utilise l'url dans la locale
     * @param string $url
     * @param string $locale
     * @param int|null $pageId page à exclure (page en cours d'édition)
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function isUniqueUrl(string $url, string $locale, ?int $pageId = null): bool
    {
        /** @var PageTranslationRepository $pageTransRepo */
        $pageTransRepo = $this->getRepository(PageTranslation::class);
        return $pageTransRepo->isUniqueUrl($url, $locale, $pageId);
    }

    /**
     * Retourne la première traduction de la page dont l'url est déjà prise dans sa locale, null sinon
     * @param Page $page
     * @return PageTranslation|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getTranslationWithDuplicateUrl(Page $page): ?PageTranslation
    {
        foreach ($page->getPageTranslations() as $translation) {
            if (
                !$this->isUniqueUrl((string) $translation->getUrl(), (string) $translation->getLocale(), $page->getId())
            ) {
                return $translation;
            }
        }
        return null;
    }

    /**
     * Retourne une liste d'élements type en fonction du type
     * @param int $type
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getListeContentByType(int $type): array
    {
        $translator = $this->getTranslator();

        $locale = $this->getLocales()['current'];
        $list = [];
        $selected = 0;
        $label = $help = '';

        switch ($type) {
            case PageContentType::FAQ->value:
                $repo = $this->getRepository(Faq::class);
                $list = $repo->getListeFaq($locale);
                $selected = array_key_first($list);
                $label = $translator->trans('page.content.faq.list', domain: 'page');
                $help = $translator->trans('page.content.faq.list.help', domain: 'page');
                break;
            case PageContentType::LISTING->value:
                $list = $this->getAllCategories();
                $selected = array_key_first($list);
                $label = $translator->trans('page.content.listing.list', domain: 'page');
                $help = $translator->trans('page.content.listing.list.help', domain: 'page');
                break;
            default:
                break;
        }

        return [
            'list' => $list,
            'selected' => $selected,
            'label' => $label,
            'help' => $help,
        ];
    }

    /**
     * Permet de retourner les info du block en fonction du type et de son typeId
     * @param int $type
     * @param int $typeId
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getInfoContentByTypeAndTypeId(int $type, int $typeId): array
    {
        $translator = $this->getTranslator();

        $typeStr = $translator->trans('page.content.type', domain: 'page') . ' : ';

        switch ($type) {
            case PageContentType::FAQ->value:
                /** @var Faq|null $faq */
                $faq = $this->findOneById(Faq::class, $typeId);
                $typeStr .= $translator->trans('page.content.type.faq', domain: 'page');
                $info =
                    $faq?->getFaqTranslationByLocale($this->getLocales()['current'])?->getTitle() ??
                    $translator->trans('page.content.info.not_found', domain: 'page');
                break;
            case PageContentType::LISTING->value:
                $typeStr .= $translator->trans('page.content.type.listing', domain: 'page');
                $category = $this->getAllCategories()[$typeId] ?? null;
                $info =
                    $category === null
                        ? $translator->trans('page.content.info.not_found', domain: 'page')
                        : $translator->trans('page.content.type.listing.info', ['type' => $category], domain: 'page');
                break;
            default:
                $typeStr .= $translator->trans('page.content.type.unknown', domain: 'page');
                $info = $translator->trans('page.content.info.unknown', domain: 'page');
        }

        return [
            'type' => $typeStr,
            'info' => $info,
        ];
    }

    /**
     * Retourne les titres et url des pages du site, toutes locales : [idPage][locale] => [title, url]
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllTitleAndUrlPage(): array
    {
        /** @var PageRepository $repository */
        $repository = $this->getRepository(Page::class);

        $return = [];
        foreach ($repository->getAllTitleAndUrl() as $row) {
            $return[$row['id']][$row['locale']] = ['title' => $row['titre'], 'url' => $row['url']];
        }
        return $return;
    }

    /**
     * Passe la page $pageId en landing page, toutes les autres ne le sont plus
     * @param int $pageId
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function switchLandingPage(int $pageId): void
    {
        /** @var PageRepository $repository */
        $repository = $this->getRepository(Page::class);
        $repository->switchLandingPage($pageId);
    }

    /**
     * Retourne une liste formatée de page pour les liens externes
     * @param string $locale
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getFormatedListePageForInternalLink(string $locale): array
    {
        $listePages = $this->findBy(Page::class, ['disabled' => false, 'status' => PageStatus::PUBLISH->value]);

        $return = [];
        foreach ($listePages as $page) {
            /** @var Page $page */
            $pageTranslation = $page
                ->getPageTranslations()
                ->filter(fn(PageTranslation $translation) => $translation->getLocale() === $locale)
                ->first();
            if (!($pageTranslation instanceof PageTranslation)) {
                continue;
            }
            $return[$page->getId()] = [
                'id' => $page->getId(),
                'title' => $pageTranslation->getTitre(),
            ];
        }
        return $return;
    }

    /**
     * Retourne la liste des titres des pages en fonction de la locale
     * @param string $locale
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getListeTitlePageByLocale(string $locale): array
    {
        $repository = $this->getRepository(Page::class);
        $result = $repository->getTitleAllPageByLocale($locale);
        $return = [];

        foreach ($result as $page) {
            $return[$page['id']] = $page['titre'];
        }
        return $return;
    }

    /**
     * Génère une URL front
     * @param Page $page
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getFrontUrl(Page $page): string
    {
        $url = $this->getOptionSystemService()->getByKey(OptionSystem::OS_ADRESSE_SITE->value)->getValue();
        $pageTrans = $page->getPageTranslationByLocale($this->getLocales()['current']);
        $category = PageCategory::tryFrom($page->getCategory())?->getSlug() ?? '';

        // Locale de la traduction réellement trouvée : page non traduite dans la locale courante
        return $url . '/' . $pageTrans?->getLocale() . '/' . $category . '/' . $pageTrans?->getUrl();
    }
}
